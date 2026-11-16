<?php
declare(strict_types=1);
namespace App;

use Closure;
use InvalidArgumentException;

final class Combinaison {
    private Closure $moveValidation;

    public function __construct (Closure $moveValidation) { $this->moveValidation = $moveValidation; }

    public function evaluate (Joueur $joueur, array $diceValue, Plateau $plateau): array {
        if (count($diceValue) !== 2) { throw new InvalidArgumentException("DEUX VALEURS DE DES SONT OBLIGATOIRES"); }
        foreach ($diceValue as $valeur) {
            if (!is_int($valeur) || $valeur < 1 || $valeur > 6) { throw new InvalidArgumentException("CHAQUE DE DOIT ETRE UN ENTIER COMPRIS ENTRE 1 ET 6"); }
        }

        [$de1, $de2] = array_values($diceValue);
        $maximum = max($de1, $de2);
        $minimum = min($de1, $de2);
        $somme = $de1 + $de2;

        $pionsActifs = [];
        $coupsIndividuels = [];
        $coupsSomme = [];

        foreach ($joueur->getPionsData() as $pawnData) {
            $pion = $joueur->getPionById($pawnData['id']);
            if ($pion === null) { continue; }
            
            $etat = $pion->getState();
            if ($etat !== ETAT_PION::ETAT_CIRCUIT && $etat !== ETAT_PION::ETAT_ARRIVEE) {
                continue;
            }
            $pionsActifs[] = $pion;
        }

        foreach ($pionsActifs as $pion) {
            foreach (array_unique([$maximum, $minimum]) as $valeur) {
                $evaluation = ($this->moveValidation)($pion, $valeur, $plateau);
                if (($evaluation['valide'] ?? false) !== true) { continue; }

                $coupsIndividuels[] = [
                    'pionId' => $pion->getId(),
                    'couleur' => $pion->getColor()->value,
                    'valeur' => $valeur,
                    'evaluation' => $evaluation,
                ];
            }

            $evaluationSomme = ($this->moveValidation)($pion, $valeur, $plateau);
            if (($evaluationSomme['valide'] ?? false) === true) {
                $coupsSomme[] = [
                    'pionId' => $pion->getId(),
                    'couleur' => $pion->getColor()->value,
                    'valeur' => $somme,
                    'evaluation' => $evaluationSomme,
                ];
            }
        }
        
        return [
            'des' => [$de1, $de2],
            'maximum' => $maximum,
            'minimum' => $minimum,
            'somme' => $somme,
            'coupsIndividuels' => $coupsIndividuels,
            'coupsSomme' => $coupsSomme,
            'reglePriorite' => [
                'maximumRechercheEnPremier' => true,
                'minimumPeutEtreUtilise' => true,
                'combinaisonsLegalesNonEliminees' => true,
            ],
            'avertissement' => "SEQUENCES DE DEUX DEPLACEMENT DOIVENT ENCORE ETRE SIMULEES ET VALIDEES APRES CHAQUE ACTION"
        ];
    }
}
?>