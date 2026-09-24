<?php
declare(strict_types=1);
namespace App;

use InvalidArgumentException;

final class Plateau {
    private const NOMBRE_CASES_CIRCUIT = 52;
    private const INDEX_DEPART = ['ROUGE' => 0, 'BLEU'  => 13, 'JAUNE' => 26, 'VERT'  => 39];
    private array $circuit = [];

    public function __construct() { $this->initializeCircuit(); }

    private function initializeCircuit (): void {
        for ($pg = 0; $pg < self::NOMBRE_CASES_CIRCUIT; $pg++) { 
            $couleurDepart = $this->getStartColor($pg);
            if ($couleurDepart !== null) { $this->circuit[$pg] = new Cases($pg, TYPE_CASE::DEPART, $couleurDepart); continue; }

            $this->circuit[$pg] = new Cases($pg, TYPE_CASE::NEUTRE);
        }
    }

    private function getStartColor (int $pg): ?COLOR {
        // foreach (self::INDEX_DEPART as $couleur => $index) {
        //     if ($pg === $index) { return COLOR::from($couleur); }
        // }
        return match ($pg) { 0 => COLOR::ROUGE, 13 => COLOR::BLEU, 26 => COLOR::JAUNE, 39 => COLOR::VERT, default => null, };
    }

    public function getCase (int $pg): Cases {
        $this->validatePG($pg);
        return $this->circuit[$pg];
    }

    // public function getCircuit (): array { return $this->circuit; }

    public function getIndexDepart(COLOR $couleur): int { return self::INDEX_DEPART[$couleur->value]; }

    public function positionToPG (int $irg, COLOR $couleur) {
        $this->validatePosition($irg);
        $index = $this->getIndexDepart($couleur);
        return ($irg + $index) % self::NOMBRE_CASES_CIRCUIT;
    }

    public function PGToPosition (int $pg, COLOR $couleur): int {
        $this->validatePG($pg);
        $index = $this->getIndexDepart($couleur);
        return ($pg - $index + self::NOMBRE_CASES_CIRCUIT) % self::NOMBRE_CASES_CIRCUIT;
    }

    private function validatePG (int $pg): void {
        if ($pg < 0 || $pg >= self::NOMBRE_CASES_CIRCUIT) { throw new InvalidArgumentException("PG DOIT ETRE COMPRISE ENTRE 0 ET 51"); }
    }

    public function calculateNextPosition (int $irg, int $deplacement): int {
        $this->validatePosition($irg);
        if ($deplacement < 0) { throw new InvalidArgumentException("DEPLACEMENT NE PEUT PAS NEGATIF"); }
        return ($irg + $deplacement) % self::NOMBRE_CASES_CIRCUIT;
    }

    private function validatePosition (int $irg): void {
        if ($irg < 0|| $irg >= self::NOMBRE_CASES_CIRCUIT) { throw new InvalidArgumentException("IRG DOIT ETRE COMPRISE ENTRE 0 ET 51"); }
    }
}
?>