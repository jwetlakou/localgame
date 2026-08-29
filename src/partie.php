<?php
namespace App;

use App\Plateau;
use App\Joueur;
use App\GestionnaireMouvement;

class Partie {
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
        $this->statut = self::STATUT_EN_COURS;
    }

    // validation et application de la transition d'etat
    public function changementEtat (string $nouvelEtat) {
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
    }

    // inscription d'un joueur
    public function addJoueur (Joueur $joueur): void { $this->joueurs[] = $joueur; }

    // recupérer le tour du joueur
    public function getJoueurActuel (): ?Joueur {
        if (empty($this->joueurs)) { return null; }
        return $this->joueurs[$this->indexJoueurActuel];
    }

    public function lancerLesDes (): array {
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
    public function jouerTour (int $idPion, int $valeurDe): array {
        if ($this->statut === self::STATUT_TERMINEE) { return ['succes' => false, 'message' => "PARTIE EST DÉJÀ TERMINÉE - VAINQUER : {$this->gagnant}"]; }
        if ($this->etatPartie !== self::ETAT_CHOIX_PION) { return ['succes' => false, 'message' => "ERREUR : PAS MOMENT DEPLACER UN PION (ETAT : {$this->etatPartie})."]; }
        $joueur = $this->getJoueurActuel();
        
        // validation propriété
        $pion = $joueur->getPionById($idPion);
        if ($pion === null) { return['succes' => false, 'message' => "PION #{$idPion} INTROUVABLE POUR CE JOUEUR {$joueur->nom}."]; }

        // validation du dé serveur
        $valeursTirees = $this->des->getValues();
        if (!in_array($valeurDe, $valeursTirees, true)) { return ['succes' => false, 'message' => "TRICHE DETECTEE : VALEUR DÉS ({$valeurDe}) N'EST PAS AU TIRAGE"]; }
        
        // detecter si il y a un coup nul
        $ordrePriorite = $this->gestionnaire->obtenirOrdrePrioriteDes($this->des);
        $deObligatoire = $ordrePriorite[0];

        $checkCoupNul = $this->gestionnaire->verifierCoupNul($joueur, $deObligatoire, $this->plateau);
        if ($checkCoupNul['coupNul']) {
            $this->changementEtat(self::ETAT_FIN_TOUR);
            $this->passerAuJoueurSuivant();
            return ['succes' => false, 'coupNul' => true, 'message' => $checkCoupNul['message'], 'prochainJoueur' => $this->getJoueurActuel()->couleur];
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
        file_put_contents($cheminFichier, serialize($this));
        return $cheminFichier;
    }

    // charger la partie
    public static function charger (string $id, string $dossier = __DIR__ . '/../storage/'): ?self {
        $cheminFichier = $dossier . $id . '.save';
        if (!file_exists($cheminFichier)) { return null; }
        return unserialize(file_get_contents($cheminFichier));
    }

    public function toArray (): array {
        $joueurActuel = $this->getJoueurActuel();

        $joueurListe = array_map(function (Joueur $joueur) {
            $pionsListe = array_map(function (Pion $pion) {
                return $pion->toArray();
            }, $joueur->pions);

            return [
                'id'        => $joueur->id,
                'nom'       => $joueur->nom,
                'couleur'   => $joueur->couleur,
                'pions'     => $pionsListe,
            ];
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
                $resultat = $this->gestionnaire->penaliserOubliCapture($pion, $this->plateau);
                if ($resultat['succes']) { $this->changementEtat(self::ETAT_FAUTE_RECLAMEE); }
                return $resultat;
            }
        }
        return ['succes' => false, 'message' => "PION #{$idPionFautif} INTROUVABLE"];
    }
}
?>