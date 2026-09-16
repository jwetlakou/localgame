<?php
namespace App;

use App\Plateau;
use App\Joueur;
use App\GestionnaireMouvement;

class Partie {
    public const MIN_JOUEURS = 2;
    public const MAX_JOUEURS = 4;

    public const ETAT_ATTENTE_LANCER    = 'ATTENTE_LANCER';
    public const ETAT_CHOIX_PION        = 'CHOIX_PION';
    public const ETAT_ANIMATION_LOADING = 'ANIMATION_LOADING';
    public const ETAT_FAUTE_RECLAMEE    = 'FAUTE_RECLAMEE';
    public const ETAT_FIN_TOUR          = 'FIN_TOUR';
    public const ETAT_PARTIE_TERMINEE   = 'PARTIE_TERMINEE';

    public string $etatPartie = self::ETAT_ATTENTE_LANCER;

    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINEE = 'TERMINEE';
    
    public string $id;
    public Plateau $plateau;
    public GestionnaireMouvement $gestionnaire;
    public Des $des;
    public array $joueurs = [];
    public int $indexJoueurActuel = 0;
    public string $statut;
    public ?string $gagnant = null;

    public function __construct (string $id = 'partie_1') {
        $this->id = $id;
        $this->plateau = new Plateau();
        $this->gestionnaire = new GestionnaireMouvement();
        $this->des = new Des();
        $this->statut = self::STATUT_EN_COURS;
    }

    // validation et application de la transition d'etat
    public function changementEtat (string $nouvelEtat):bool {
        $transitionsValides = [
            self::ETAT_ATTENTE_LANCER       => [self::ETAT_CHOIX_PION, self::ETAT_FAUTE_RECLAMEE],
            self::ETAT_CHOIX_PION           => [self::ETAT_ANIMATION_LOADING, self::ETAT_FAUTE_RECLAMEE, self::ETAT_FIN_TOUR],
            self::ETAT_ANIMATION_LOADING    => [self::ETAT_FIN_TOUR, self::ETAT_CHOIX_PION, self::ETAT_PARTIE_TERMINEE],
            self::ETAT_FAUTE_RECLAMEE       => [self::ETAT_ATTENTE_LANCER, self::ETAT_FIN_TOUR],
            self::ETAT_FIN_TOUR             => [self::ETAT_ATTENTE_LANCER],
        ];

        if (isset($transitionsValides[$this->etatPartie]) && in_array($nouvelEtat, $transitionsValides[$this->etatPartie], true)) {
            $this->etatPartie = $nouvelEtat;
            return true;
        }
        return false;
    }

    // inscription d'un joueur
    public function addJoueur (Joueur $joueur): void {
        if (count($this->joueurs) >= self::MAX_JOUEURS) {
            throw new \LogicException("REGLE VIOLÉE - une partie peut pas avoir plus de " . self::MAX_JOUEURS . " joueurs.");
        }
        $this->joueurs[] = $joueur;
    }

    // recupérer le tour du joueur
    public function getJoueurActuel (): ?Joueur {
        if (empty($this->joueurs)) { return null; }
        return $this->joueurs[$this->indexJoueurActuel];
    }

    public function lancerLesDes (): array {
        if (count($this->joueurs) < self::MIN_JOUEURS) { return [ 'succes' => false, 'message' => "ACTION NON AUTORISÉE - une partie necessite au moins " . self::MIN_JOUEURS . " joueurs." ]; }
        
        if ($this->etatPartie !== self::ETAT_ATTENTE_LANCER) { return ['erreur' => true, 'message' => "ACTION NON AUTORISEE - ETAT ACTUEL {$this->etatPartie}"]; }
        
        $desLances = $this->des->lancer();
        $ordrePriorite = $this->gestionnaire->obtenirOrdrePrioriteDes($this->des);
        $this->changementEtat(self::ETAT_CHOIX_PION);

        return [
            'de1'       => $desLances['de1'],
            'de2'       => $desLances['de2'],
            'isDouble'  => $desLances['isDouble'],
            'priorite'  => $ordrePriorite,
            'etatPartie'=> $this->etatPartie,
        ];
    }

    // passe la main au joueur suivant
    public function passerAuJoueurSuivant (): Joueur {
        if (empty($this->joueurs)) { throw new \Exception("aucun joueur n'a ajouté à la partie"); }
        $this->indexJoueurActuel = ($this->indexJoueurActuel + 1) % count($this->joueurs);
        return $this->getJoueurActuel();
    }

    // l'execution complète
    public function jouerTour (int $joueurId, int $idPion, int $valeurDe): array {
        if ($this->statut === self::STATUT_TERMINEE) { return ['succes' => false, 'message' => "PARTIE EST DÉJÀ TERMINÉE - VAINQUER : {$this->gagnant}"]; }
        if ($this->etatPartie !== self::ETAT_CHOIX_PION) { return ['succes' => false, 'message' => "ERREUR : PAS MOMENT DEPLACER UN PION (ETAT : {$this->etatPartie})."]; }
        $joueur = $this->getJoueurActuel();

        if ($joueur === null || $joueur->id !== $joueurId) {
            $nom = $joueur ? $joueur->nom : 'inconnu';
            return [ 'succes' => false, 'message' => "ACTION REFUSÉE - TOUR JOUEUR {$nom} ! PAS #{$joueurId}." ];
        }
        
        // validation propriété
        $pion = $joueur->getPionById($idPion);
        if ($pion === null) { return['succes' => false, 'message' => "PION #{$idPion} INTROUVABLE POUR CE JOUEUR {$joueur->nom}."]; }

        if ($pion->isStacked) { return [ 'succes' => false, 'message' => "ACTION REFUSÉE - PION #{$idPion} EST SOUS UNE PILE." ]; }

        // validation du dé serveur
        $valeursTirees = $this->des->getValues();
        if (!in_array($valeurDe, $valeursTirees, true)) { return ['succes' => false, 'message' => "TRICHE DETECTEE : VALEUR DÉS ({$valeurDe}) N'EST PAS AU TIRAGE"]; }
        
        // gestion de la priorité des dés (tente le dé prioritaire, puis le dé secondaire si nécessaire)
        $ordrePriorite = $this->gestionnaire->obtenirOrdrePrioriteDes($this->des);
        $deObligatoire = $ordrePriorite[0];

        // détection du coup nul
        $checkCoupNul = $this->gestionnaire->verifierCoupNul($joueur, $deObligatoire, $this->plateau);

        if ($checkCoupNul['coupNul'] && count($ordrePriorite) > 1) {
            $deSecondaire = $ordrePriorite[1];
            $checkCoupNulMin = $this->gestionnaire->verifierCoupNul($joueur, $deSecondaire, $this->plateau);
            
            if ($checkCoupNulMin['coupNul']) {
                $this->changementEtat(self::ETAT_FIN_TOUR);
                $this->passerAuJoueurSuivant();
                return ['succes' => false, 'coupNul' => true, 'message' => $checkCoupNul['message'], 'prochainJoueur' => $this->getJoueurActuel()->couleur];
            }
        }

        // exécuter le mouvement du pion
        $resultat = $this->gestionnaire->executerMouvement($pion, $valeurDe, $this->plateau);
        if ($resultat['succes']) {
            // verifier si il y a victoire
            $victoire = $this->gestionnaire->verifierVictoire($joueur);
            // passe à l'étape potentielle reclamation de faute
            $this->changementEtat(self::ETAT_FIN_TOUR);

            if ($victoire['victoire']) {
                $this->statut = self::STATUT_TERMINEE;
                $this->gagnant = $joueur->nom;
                $resultat['victoire'] = true;
                $resultat['messageVictoire'] = $victoire['message'];
            } else {
                if ($valeurDe !== 6) { $this->passerAuJoueurSuivant(); }
            }
        }

        $resultat['prochainJoueur'] = $this->getJoueurActuel()->nom;
        return $resultat;
    }

    // sauvegarde la partie
    public function sauvegarder (string $dossier = __DIR__ . '/../storage/'): string {
        if (!is_dir($dossier)) { mkdir($dossier, 0777, true); }
        $cheminFichier = $dossier . $this->id . '.save';
        $data = $this->toArray();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($cheminFichier, $json);
        return $cheminFichier;
    }

    // charger la partie
    public static function charger (string $id, string $dossier = __DIR__ . '/../storage/'): ?self {
        $cheminFichier = $dossier . $id . '.save';
        if (!file_exists($cheminFichier)) { return null; }

        $contenu = file_get_contents($cheminFichier);
        $data = json_decode($contenu, true);
        // $data = unserialize($contenu, [
        //     'allowed_classes' => [
        //         self::class,
        //         Plateau::class,
        //         GestionnaireMouvement::class,
        //         Des::class,
        //         Joueur::class,
        //         Pion::class,
        //         Cases::class
        //     ]
        // ]);
        if (!is_array($data)) { return null; }
        return self::fromArray($data);
        // return ($partieReestauree instanceof self) ? $partieReestauree : null;
    }

    public static function fromArray (array $data): self {
        $partie = new self($data['idPartie'] ?? 'partie_1');
        $partie->statut = $data['statut'] ?? self::STATUT_EN_COURS;
        $partie->etatPartie = $data['etatPartie'] ?? self::ETAT_ATTENTE_LANCER;
        $partie->gagnant = $data['gagnant'] ?? null;

        // reconstruction des joueurs
        $partie->joueurs = [];
        if (isset($data['joueurs']) & is_array($data['joueurs'])) {
            foreach ($data['joueurs'] as $jData) {
                $joueur = new Joueur($jData['id'], $jData['nom'], $jData['couleur']);

                if (isset($jData['pions']) && is_array($jData['pions'])) {
                    foreach ($jData['pions'] as $index => $pData) {
                        $pionTarget = $joueur->getPionById($pData['id'] ?? ($index + 1));
                        if ($pionTarget !== null) {
                            $pionTarget->position = $pData['position'];
                            $pionTarget->etat = $pData['etat'];
                            $pionTarget->isStacked = $pData['estEmpile'];
                        }
                    }
                }
                $partie->joueurs[] = $joueur;
            }
        }

        // restauration de l'index du joueur actif
        if (isset($data['joueurActif']['id'])) {
            foreach ($partie->joueurs as $index => $ja) {
                if ($ja->id === $data['joueurActif']['id']) {
                    $partie->indexJoueurActuel = $index;
                    break;
                }
            }
        }

        // restauration 
        if (isset($data['des']['valeurs'])) {
            $partie->des->de1 = $data['des']['valeurs'][0] ?? 0;
            $partie->des->de2 = $data['des']['valeurs'][0] ?? 0;
        }

        return $partie;
    } 

    public function toArray (): array {
        $joueurActuel = $this->getJoueurActuel();

        $joueurListe = array_map(function (Joueur $joueur) {
            return $joueur->toArray();
        }, $this->joueurs);
        
        return [
            'idPartie'      => $this->id,
            'statut'        => $this->statut,
            'joueurActuel'  => $joueurActuel ? [
                'id'        => $joueurActuel->id,
                'nom'       => $joueurActuel->nom,
                'couleur'   => $joueurActuel->couleur,
            ] : null,
            'des'           => [
                'valeurs'   => [$this->des->de1 ?? 0, $this->des->de2 ?? 0],
                'priorite'  => $this->gestionnaire->obtenirOrdrePrioriteDes($this->des),
            ],
            'joueurs'       => $joueurListe,
            'gagnant'       => $this->gagnant
        ];
    }

    // validation et execution de la reclamation d'une faute
    public function reclamerFaute (int $idPionFautif): array {
        if ($this->statut === self::STATUT_TERMINEE) { return ['succes' => false, 'message' => "PARTIE DÉJÀ TERMINÉE"]; }

        foreach ($this->joueurs as $joueur) {
            $pion = $joueur->getPionById($idPionFautif);
            if ($pion !== null) {
                if ($pion->isStacked) { return [ 'succes' => false, 'message' => "RECLAMATION REFUSÉE - PION #{$idPionFautif} EST SOUS UNE PILE." ]; }
                $resultat = $this->gestionnaire->penaliserOubliCapture($pion, $this->plateau);
                if ($resultat['succes']) { $this->changementEtat(self::ETAT_FAUTE_RECLAMEE); }
                return $resultat;
            }
        }
        return ['succes' => false, 'message' => "PION #{$idPionFautif} INTROUVABLE"];
    }
}
?>