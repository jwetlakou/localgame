<?php
namespace App;

use App\Des;
use App\Plateau;
use App\Pion;

class GestionnaireMouvement {
    public function obtenirOrdrePrioriteDes (Des $des): array {
        $max = $des->getDeMax(); $min = $des->getDeMin();
        return [$max, $min];
    }

    public function evaluerMouvement (Pion $pion, int $valeurDe, Plateau $plateau): array {
        // pion en OTAGE peut pas deplacer
        if ($pion->etat === Pion::ETAT_OTAGE) { return ['valide' => false, 'message' => "COUP IMPOSSIBLE : PION EN OTAGE"]; }

        // pion à MAISON et besoin d'un 6
        if ($pion->etat === Pion::ETAT_MAISON && $valeurDe !==6) { return ['valide' => false, 'message' => "COUP IMPOSSIBLE : PION EN MAISON, BESOIN D'UN 6"]; }
        
        // vérification si le pion est sur le circuit et au sommet de la pile
        if ($pion->etat === Pion::ETAT_CIRCUIT) {
            $caseActuelle = $plateau->getCaseCircuit($pion->position);

            if ($caseActuelle !== null && count($caseActuelle->pions) > 1) {
                $pionOnSommet = $caseActuelle->getPionOnSommet();
                
                if ($pionOnSommet !== null && $pionOnSommet->id !== $pion->id) { return ['valide' => false, 'message' => "COUP IMPOSSIBLE : PION BLOQUE"]; }
            }
        }
        
        // règle du coup exact : interdiction de depasser la ligne d'ARRIVÉE
        if ($pion->etat === Pion::ETAT_ARRIVEE) {
            $distanceRestante = 6 - $pion->position;

            if ($valeurDe > $distanceRestante) { return ['valide' => false, 'message' => "COUP IMPOSSIBLE : {$valeurDe} DEPASSE LA DISTANCE ({$distanceRestante})"]; }
        }

        // calcul de la destination
        $calcul = $plateau->calculerProchainePosition($pion, $valeurDe);
        return ['valide' => true, 'details' => $calcul];
    }

    public function executerMouvement (Pion $pion, int $valeurDe, Plateau $plateau): array {
        $evaluation = $this->evaluerMouvement($pion, $valeurDe, $plateau);
        if (!$evaluation['valide']) { return ['succes' => false, 'message' => $evaluation['message']]; }

        $details = $evaluation['details'];
        $anciennePos = $pion->position;
        $ancienEtat = $pion->etat;

        // retirer un pion déplacant dans son ancienne case
        if ($ancienEtat === Pion::ETAT_CIRCUIT) {
            $caseAncienne = $plateau->getCaseCircuit($anciennePos);
            if ($caseAncienne !== null) { $caseAncienne->depiler(); }
        } elseif ($ancienEtat === Pion::ETAT_ARRIVEE) {
            $caseAncienne = $plateau->getCaseArrivee($pion->couleur, $anciennePos);
            if ($caseAncienne !== null) { $caseAncienne->depiler(); }
        }

        // gestion de la capture sur le circuit
        $captureEffectuees = [];
        if ($details['nouvelEtat'] === Pion::ETAT_CIRCUIT) {
            $caseDestination = $plateau->getCaseCircuit($details['nouvellePos']);

            // cas s'il y a déjà un pions de couleur differente sur la case
            if ($caseDestination !== null && !$caseDestination->isEmpty()) {
                $pionSommetAdverse = $caseDestination->getPionOnSommet();

                if ($pionSommetAdverse !== null && $pionSommetAdverse->couleur !== $pion->couleur) {
                    // case neutre : on mange
                    if ($caseDestination->type === Cases::TYPE_NEUTRE) {
                        while (!$caseDestination->isEmpty()) {
                            $pionMange = $caseDestination->depiler();

                            if ($pionMange !== null) {
                                $pionMange->deplacerVers(-1, Pion::ETAT_OTAGE);
                                $captureEffectuees[] = [
                                    'pionId'    => $pionMange->id,
                                    'couleur'   => $pionMange->couleur,
                                    'nouvelEtat'   => Pion::ETAT_OTAGE
                                ];
                            }
                        }
                    }
                    // case DEPART : on mange pas
                }
            }
        }

        // mise a jour de la position du pion déplacant
        $pion->deplacerVers($details['nouvellePos'], $details['nouvelEtat']);

        // placer le pion déplacant sur la nouvelle case
        if ($details['nouvelEtat'] === Pion::ETAT_CIRCUIT) {
            $caseNouvelle = $plateau->getCaseCircuit($details['nouvellePos']);
            if ($caseNouvelle !== null) { $caseNouvelle->empiler($pion); }
        } elseif ($details['nouvelEtat'] === Pion::ETAT_ARRIVEE) {
            $caseNouvelle = $plateau->getCaseArrivee($pion->couleur, $details['nouvellePos']);
            if ($caseNouvelle !== null) { $caseNouvelle->empiler($pion); }
        } elseif ($details['nouvelEtat'] === Pion::ETAT_CENTRE) {
            $plateau->centre->empiler($pion);
        }

        return [
            'succes'            => true,
            'pionId'            => $pion->id,
            'couleur'           => $pion->couleur,
            'deJoue'            => $valeurDe,
            'depart'            => ['position' => $anciennePos, 'etat' => $ancienEtat],
            'arrivee'           => ['position' => $pion->position, 'etat' => $pion->etat],
            'nombreCaptures'    => count($captureEffectuees),
            'detailsCaptures'   => $captureEffectuees,
            'message'           => $details['message']
        ];
    }

    public function executerTourComplet (Pion $pionA, int $de1, Pion $pionB, int $de2, Plateau $plateau): array {
        $resAction1 = $this->evaluerMouvement($pionA, $de1, $plateau);
        $resAction2 = $this->evaluerMouvement($pionB, $de2, $plateau);

        return [
            'tourReussie'   => ($resAction1['succes'] && $resAction2['succes']),
            'action1'       => ['pionId' => $pionA->id, 'de' => $de1, 'resultat' => $resAction1],
            'action2'       => ['pionId' => $pionB->id, 'de' => $de2, 'resultat' => $resAction2],
        ];
    }

    public function libererOtage (Pion $pionOtage, int $valeurDe, Joueur $joueurPreneur): array {
        // vérification que le pion est bien un otage
        if ($pionOtage->etat !== Pion::ETAT_OTAGE) { return ['succes' => false, 'message' => "PION N'EST PAS EN OTAGE"]; }

        // vérification que la rancon est payée avec 6
        if ($valeurDe !== 6) { return ['succes' => false, 'message' => "ECHEC DE RANCON : UN 6 POUR LIBERER OTAGE"]; }
        
        // libération du pion : retour à MAISON
        $pionOtage->deplacerVers(-1, Pion::ETAT_MAISON);
        return [
            'succes'        => true,
            'pionId'        => $pionOtage->id,
            'couleurOtage'  => $pionOtage->couleur,
            'nouvelEtat'    => Pion::ETAT_MAISON,
            'beneficiaire'  => [
                'nomJoueur'     => $joueurPreneur->nom,
                'couleur'       => $joueurPreneur->couleur,
                'valeurDe'      => 6,
                'instruction'   => "BENEFICIE DE JOUER SON OU SES PIONS",
            ],
            'message'       => "RANCON PAYEE, PION {$pionOtage->couleur} RENTRE à MAISON, {$joueurPreneur->nom} JOUE LE LANCER"
        ];
    }

    public function aDesCoupsPossibles (Joueur $joueur, int $valeurDe, Plateau $plateau): bool {
        foreach ($joueur->pions as $pion) {
            $evaluation = $this->evaluerMouvement($pion, $valeurDe, $plateau);
            if ($evaluation['valide']) { return true; }
        }
        return false;
    }

    public function verifierCoupNul (Joueur $joueur, int $valeurDe, Plateau $plateau): array {
        $coupPossible = $this->aDesCoupsPossibles($joueur, $valeurDe, $plateau);
        if (!$coupPossible) { return ['coupNul' => true, 'message' => "COUP NUL POUR {$joueur->nom} ({$joueur->couleur})"]; }

        return ['coupNul' => false, 'message' => "COUP VALIDE POUR {$joueur->nom} ({$joueur->couleur})"];
    }

    public function penaliserOubliCapture (Pion $pionFautif, Plateau $plateau): array {
        // retirer le pion fautif de sa case actuelle
        if ($pionFautif->etat === Pion::ETAT_CIRCUIT) {
            $case = $plateau->getCaseCircuit($pionFautif->position);
            if ($case !== null) { $case->depiler(); }
        } elseif ($pionFautif->etat === Pion::ETAT_ARRIVEE) {
            $case = $plateau->getCaseArrivee($pionFautif->couleur, $pionFautif->position);
            if ($case !== null) { $case->depiler(); }
        }

        // renvoyer directement à MAISON
        $pionFautif->deplacerVers(-1, Pion::ETAT_MAISON);
        return [
            'succes'        => true,
            'pionPenalise'  => $pionFautif->id,
            'couleur'       => $pionFautif->couleur,
            'nouvelEtat'    => Pion::ETAT_MAISON,
            'message'       => "FAUTE RECLAMÉE ! ({$pionFautif->couleur}) #{$pionFautif->id} N'A PAS MANGE ET EST RENVOYE A LA MAISON"
        ];
    }

    public function verifierVictoire (Joueur $joueur): array {
        if ($joueur->isWin()) { return ['victoire' => true, 'gagnant' => $joueur->nom, 'couleur' => $joueur->couleur, 'message' => "🎉VICTOIRE ! JOUEUR {$joueur->nom} ({$joueur->couleur}) A 4 PIONS AU CENTRE"]; }
        return ['victoire' => false, 'gagnant' => null, 'message' => "LA PARTIE CONTINUE POUR {$joueur->nom} !"];
    }
}
?>