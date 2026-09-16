<?php
namespace App;
use App\Cases;

require_once __DIR__ . '/cases.php';

class Plateau {
    public const COULEUR = ['ROUGE', 'BLEU', 'JAUNE', 'VERT'];

    public const INDEX_DEPART = [
        'ROUGE' => 0,
        'BLEU'  => 13,
        'JAUNE' => 26,
        'VERT'  => 39
    ];

    public array $circuit = [];

    public array $voiesArrivee = [];

    public Cases $centre;

    public function __construct () {
        $this->initialiser();
    }
    
    private function initialiser (): void {
        for ($i = 0; $i < 52; $i++) {
            $type = Cases::TYPE_NEUTRE;
            $colorSecteur = null;

            $colorDepart = array_search($i, self::INDEX_DEPART, true);

            if ($colorDepart !== false) {
                $type = Cases::TYPE_DEPART;
                $colorSecteur = $colorDepart;
            }

            $this->circuit[$i] = new Cases($i, $type, $colorSecteur);
        }

        foreach (self::COULEUR as $couleur) {
            $this->voiesArrivee[$couleur] = [];
            for ($j = 1; $j <= 6; $j++) { $this->voiesArrivee[$couleur][$j]= new Cases($j, Cases::TYPE_ARRIVEE, $couleur); }
        }

        $this->centre = new Cases(999, Cases::TYPE_CENTRE);
    }

    public function calculerNouvellePosition (int $posActuelle, int $de): int {
        return ($posActuelle + $de) % 52;
    }

    public function calculerProchainePosition (Pion $pion, int $de): array {
        // sortie de maison
        if ($pion->etat === Pion::ETAT_MAISON) {
            if ($de === 6) {
                $posDepart = self::INDEX_DEPART[$pion->couleur];

                return [
                    'valide'        => true,
                    'nouvelEtat'    => Pion::ETAT_CIRCUIT,
                    'nouvellePos'   => $posDepart,
                    'typeZone'      => 'CIRCUIT',
                    'message'       => 'SORTIE DE MAISON'
                ];
            }
            return [
                'valide'        => false,
                'nouvelEtat'    => Pion::ETAT_MAISON,
                'nouvellePos'   => -1,
                'typeZone'      => 'MAISON',
                'message'       => 'IMPOSSIBLE DE SORTIR SANS 6'
            ];
        }

        // pion déjà sur voie arrivée
        if ($pion->etat === Pion::ETAT_ARRIVEE) {
            $nouvellePosArrivee = $pion->position + $de;
            
            if ($nouvellePosArrivee === 6) {
                return [
                    'valide'        => true,
                    'nouvelEtat'    => Pion::ETAT_CENTRE,
                    'nouvellePos'   => 999,
                    'typeZone'      => 'CENTRE',
                    'message'       => 'PION EST AU CENTRE'
                ];
            } elseif ($nouvellePosArrivee < 6) {
                return [
                    'valide'        => true,
                    'nouvelEtat'    => Pion::ETAT_ARRIVEE,
                    'nouvellePos'   => $nouvellePosArrivee,
                    'typeZone'      => 'ARRIVEE',
                    'message'       => "'VOIE DE SECURITÉ"
                ];
            } else {
                return [
                    'valide'        => false,
                    'nouvelEtat'    => Pion::ETAT_ARRIVEE,
                    'nouvellePos'   => $pion->position,
                    'typeZone'      => 'ARRIVEE',
                    'message'       => "VALEUR DEPASSÉE LE CENTRE"
                ];
            }
        }

        // pion sur le circuit
        if ($pion->etat === Pion::ETAT_CIRCUIT) {
            $indexDepart = self::INDEX_DEPART[$pion->couleur];

            $casesParcourues = ($pion->position - $indexDepart + 52) % 52;
            $nouvelleCasesParcourues = $casesParcourues + $de;

            if ($nouvelleCasesParcourues > 51) {
                $posVoieSecurite = $nouvelleCasesParcourues - 51;

                if ($posVoieSecurite <= 5) {
                    return [
                        'valide'        => true,
                        'nouvelEtat'    => Pion::ETAT_ARRIVEE,
                        'nouvellePos'   => $posVoieSecurite,
                        'typeZone'      => 'ARRIVEE',
                        'message'       => "ENTRÉE SUR LA VOIE DE SECURITÉ"
                    ];
                } elseif ($posVoieSecurite == 6) {
                    return [
                        'valide'        => true,
                        'nouvelEtat'    => Pion::ETAT_CENTRE,
                        'nouvellePos'   => 999,
                        'typeZone'      => 'CENTRE',
                        'message'       => "PION EST DANS CENTRE"
                    ];
                } else {
                    return [
                        'valide'        => false,
                        'nouvelEtat'    => Pion::ETAT_CIRCUIT,
                        'nouvellePos'   => $pion->position,
                        'typeZone'      => 'CIRCUIT',
                        'message'       => "COUP IMPOSSIBLE : LE DEPASSEMENT DU CENTRE EST INTERDIT"
                    ];
                }
            }
            
            // progession pion sur le circuit
            $nouvellePosCircuit = ($pion->position + $de) % 52;
            return [
                'valide'        => true,
                'nouvelEtat'    => Pion::ETAT_CIRCUIT,
                'nouvellePos'   => $nouvellePosCircuit,
                'typeZone'      => 'CIRCUIT',
                'message'       => "DÉPLACEMENT SUR CIRCUIT SUR VERS CASE {$nouvellePosCircuit}"
            ];
        }
        return [
            'valide'        => true,
            'nouvelEtat'    => $pion->etat,
            'nouvellePos'   => $pion->position,
            'typeZone'      => 'INCONNU',
            'message'       => "ACTION NON GÉRÉE"
        ];
    }

    public function calculerDistance (int $posDepart, int $posCible): int {
        return ($posCible - $posDepart + 52) %52;
    }

    public function distanceProchainDepart (int $posActuelle): array {
        $distanceMin = 52;
        $prochaineCouleur = '';

        foreach (self::INDEX_DEPART as $couleur => $indexDepart) {
            $dist = $this->calculerDistance($posActuelle, $indexDepart);

            if ($dist === 0) { $dist = 13; }

            if ($dist < $distanceMin) {
                $distanceMin = $dist;
                $prochaineCouleur = $couleur;
            }
        }

        return [
            'distance'      => $distanceMin,
            'couleurDepart' => $prochaineCouleur,
            'indexProchain' => self::INDEX_DEPART[$prochaineCouleur],
        ];
    }

    public function getCaseCircuit (int $index): ?Cases {
        return $this->circuit[$index] ?? null;
    }

    public function getCaseDepart (string $couleur): ?Cases {
        $index = self::INDEX_DEPART[$couleur] ?? null;
        return $index !== null ? $this->circuit[$index] : null;
    }

    public function getCaseArrivee (string $couleur, int $indexArrivee): ?Cases {
        return $this->voiesArrivee[$couleur][$indexArrivee] ?? null; 
    }

    public function toArray (): array {
        $circuitArray = [];
        foreach ($this->circuit as $case) { $circuitArray[] = $case->toArray(); }

        $voiesArray = [];
        foreach ($this->voiesArrivee as $couleur => $cases) {
            $voiesArray[$couleur] = [];
            foreach ($cases as $index => $case) { $voiesArray[$couleur][$index] = $case->toArray(); }
        }

        return [
            'nbCasesCircuit'    => count($this->circuit),
            'indexDepart'       => self::INDEX_DEPART,
            'circuit'           => $circuitArray,
            'voiesArrivee'      => $voiesArray,
            'centre'            => $this->centre->toArray()
        ];
    }
}
?>