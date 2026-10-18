<?php
declare(strict_types=1);
namespace App;

use InvalidArgumentException;
use LogicException;

final class Plateau {
    private const NOMBRE_CASES_PLATEAU = 76;
    private const NOMBRE_CASES_CIRCUIT = 52;
    
    private const POSITION_MIN = 0;
    private const POSITION_MAX = 57;
    
    private const ARRIVEE_MIN = 52;
    private const ARRIVEE_MAX = 56;
    
    private const POSITION_CENTRE = 57;

    private const INDEX_DEPART = ['ROUGE' => 0, 'BLEU'  => 13, 'JAUNE' => 26, 'VERT'  => 39];
    private const PG_ARRIVEE = ['ROUGE' => 52, 'BLEU'  => 57, 'JAUNE' => 62, 'VERT'  => 67];
    private const PG_CENTRE = ['ROUGE' => 72, 'BLEU'  => 73, 'JAUNE' => 74, 'VERT'  => 75];

    private array $cases = [];

    public function __construct() {
        $this->initializeCircuit();
        $this->initializeArrivee();
        $this->initializeCentre();
        $this->assertGeometry();
    }

    private function validateCircuitPosition (int $irg): void {
        if ($irg  < 0 || $irg > 51) { throw new InvalidArgumentException("POSITION DU CIRCUIT DOIT COMPRISE ENTRE 0 ET 51"); }
    }

    private function validatePG (int $pg): void {
        if ($pg < 0 || $pg >= self::NOMBRE_CASES_PLATEAU) { throw new InvalidArgumentException("PG DOIT ETRE COMPRISE ENTRE 0 ET 75"); }
    }

    private function validatePosition (int $position): void {
        if ($position < self::POSITION_MIN || $position > self::POSITION_MAX) { throw new InvalidArgumentException("POSITION DOIT ETRE COMPRISE ENTRE 0 ET 57"); }
    }

    private function assertGeometry (): void {
        if (count($this->cases) !== self::NOMBRE_CASES_PLATEAU) { throw new LogicException("LA GÉOMETRIE DU PLATEAU DOIT CONTENIR EXACTEMENT 76"); }
        for ($pg = 0; $pg < self::NOMBRE_CASES_PLATEAU; $pg++) {
            if (!isset($this->cases[$pg])) {throw new LogicException("CASE PG {$pg} MANQUANTE DANS LA GÉOMETRIE"); }
            if ($this->cases[$pg]->getId() !== $pg) { throw new LogicException("INCOHERENCE ENTRE CLE PG {$pg} ET ID DE LA CASE"); }
        }
        $this->assertCircuitGeometry();
        $this->assertArriveeGeometry();
        $this->assertCentreGeometry();

        $this->assertConversionGeometry();
    }

    private function assertCircuitGeometry (): void {
        for ($pg = 0; $pg < 52; $pg++) {
            $case = $this->cases[$pg]; $startColor = $this->getStartColor($pg);

            if ($startColor !== null) {
                if ($case->getType() !== TYPE_CASE::DEPART) { throw new LogicException("PG {$pg} DOIT ETRE UN DEPART"); }
                if ($case->getColorSector() !== $startColor) { throw new LogicException("PG {$pg} POSSEDE UN MAUVAIS SECTEUR"); }
                continue;
            }
            if ($case->getType() !== TYPE_CASE::NEUTRE) { throw new LogicException("PG {$pg} DOIT ETRE NEUTRE"); }
            if ($case->getColor !== null) { throw new LogicException("PG {$pg} NEUTRE NE DOIT PAS POSSEDER DE SECTEUR"); }
        }
    }

    private function assertArriveeGeometry(): void {
        foreach (COLOR::cases() as $color) {
            $startPG = self::PG_ARRIVEE[$color->value];

            for ($offset = 0; $offset <= 4; $offset++) {
                $pg = $startPG + $offset;
                $case = $this->cases[$pg];
                if ($case->getType() !== TYPE_CASE::ARRIVEE) { throw new LogicException("PG {$pg} DOIT ETRE UNE ARRIVEE"); }
                if ($case->getColorSector !== $color) { throw new LogicException("PG {$pg} DOIT APPARTENIR AU SECTEUR {$color->value}"); }
            }
        }
    }

    private function assertCentreGeometry (): void {
        foreach (COLOR::cases() as $color) {
            $pg = self::PG_CENTRE[$color->value];
            $case = $this->cases[$pg];

            if ($case->getType() !== TYPE_CASE::CENTRE) { throw new LogicException("PG {$pg} DOIT ETRE UN CENTRE"); }
            if ($case->getColorSector !== $color) { throw new LogicException("PG {$pg} DOIT APPARTENIR AU CENTRE {$color->value}"); }
        }
    }

    private function assertConversionGeometry (): void {
        foreach (COLOR::cases() as $color) {
            for ($irg = self::POSITION_MIN; $irg <= self::POSITION_MAX; $irg++) {
                $pg = $this->positionToPG($irg, $color);
                $position = $this->PGToPosition($pg, $color);

                if ($position !== $irg) { throw new LogicException("CONVERSION IRG → PG → IRG INCOHERENTE POUR {$color->value}, IRG {$irg}"); }
            }
        }

        for ($pg = 0; $pg < self::NOMBRE_CASES_PLATEAU; $pg++) {
            $case = $this->cases[$pg];
            $color = $case->getColorSector();

            if ($case->getType() === TYPE_CASE::NEUTRE) {
                foreach (COLOR::cases() as $pawnColor) {
                    $irg = $this->PGToPosition($pg, $pawnColor);
                    $index = $this->positionToPG($irg, $pawnColor);

                    if ($index !== $pg) { throw new  LogicException("CONVERSION PG → IRG → PG INCOHERENTE POUR PG {$pg} ET {$pawnColor->value}"); }
                }
                continue;
            }
            if ($color === null) { throw new LogicException("CASE SPECIALE DOIT POSSEDER UNE COULEUR"); }
            $irg = $this->PGToPosition($pg, $color);
            $index = $this->positionToPG($irg, $color);

            if ($index !== $pg) { throw new LogicException("CONVERSION PG → IRG → PG INCOHERENTE POUR PG {$pg}"); }
        }
    }

    private function initializeCircuit (): void {
        for ($pg = 0; $pg < self::NOMBRE_CASES_CIRCUIT; $pg++) { 
            $couleurDepart = $this->getStartColor($pg);
            if ($couleurDepart !== null) { $this->cases[$pg] = new Cases($pg, TYPE_CASE::DEPART, $couleurDepart); continue; }

            $this->cases[$pg] = new Cases($pg, TYPE_CASE::NEUTRE);
        }
    }

    private function initializeArrivee (): void {
        foreach (COLOR::cases() as $couleur) {
            $pgStart = self::PG_ARRIVEE[$couleur->value];
            for ($offset = 0; $offset <= 4; $offset++) {
                $pg = $pgStart + $offset;
                $this->cases[$pg] = new Cases($pg, TYPE_CASE::ARRIVEE, $couleur);
            }
        }
    }

    private function initializeCentre (): void {
        foreach (COLOR::cases() as $couleur) {
            $pg = self::PG_CENTRE[$couleur->value];
            $this->cases[$pg] = new Cases($pg, TYPE_CASE::CENTRE, $couleur);
        }
    }

    private function getStartColor (int $pg): ?COLOR {
        // $this->validatePG($pg);
        return match ($pg) { 0 => COLOR::ROUGE, 13 => COLOR::BLEU, 26 => COLOR::JAUNE, 39 => COLOR::VERT, default => null };
    }

    public function getCase (int $pg): Cases {
        $this->validatePG($pg);
        return $this->cases[$pg];
    }

    public function getIndexDepart(COLOR $couleur): int { return self::INDEX_DEPART[$couleur->value]; }

    public function positionToPG (int $irg, COLOR $couleur): int {
        $this->validatePosition($irg);
        if ($irg <= self::NOMBRE_CASES_CIRCUIT - 1) {
            $index = $this->getIndexDepart($couleur);
            return ($irg + $index) % self::NOMBRE_CASES_CIRCUIT;
        }

        if ($irg <= self::ARRIVEE_MAX) {
            $startA = self::PG_ARRIVEE[$couleur->value];
            return $startA + ($irg - self::ARRIVEE_MIN);
        }

        return self::PG_CENTRE[$couleur->value];
    }

    public function PGToPosition (int $pg, COLOR $couleur): int {
        $this->validatePG($pg);
        if ($pg < self::NOMBRE_CASES_CIRCUIT) {
            $index = $this->getIndexDepart($couleur);
            return ($pg - $index + self::NOMBRE_CASES_CIRCUIT) % self::NOMBRE_CASES_CIRCUIT;
        }

        if ($pg <= 71) {
            $startA = self::PG_ARRIVEE[$couleur->value]; $endA = $startA + 4;
            if ($pg < $startA || $pg > $endA) { throw new InvalidArgumentException("PG ARRIVÉE N'APPARTIENT PAS A LA COULEUR DEMANDÉE"); }
            return self::ARRIVEE_MIN + ($pg - $startA);
        }

        $startC = self::PG_CENTRE[$couleur->value];
        if ($pg !== $startC) { throw new InvalidArgumentException("PG DU CENTRE N'APPARTIENT PAS A LA COULEUR DEMANDÉE"); }
        return self::POSITION_CENTRE;
    }

    public function calculateNextPosition (int $irg, int $deplacement): int {
        $this->validatePosition($irg);
        if ($deplacement < 0) { throw new InvalidArgumentException("DEPLACEMENT NE PEUT PAS NEGATIF"); }
        if ($irg === self::POSITION_CENTRE && $deplacement > 0) { throw new InvalidArgumentException("PION ARRIVÉ AU CENTRE PEUT PLUS SE DEPLACER"); }
        $nouvellePosition = $irg + $deplacement;

        if ($nouvellePosition > self::POSITION_CENTRE) { throw new InvalidArgumentException("DEPLACEMENT DEPASSE LA POSITION DU CENTRE"); }
        return $nouvellePosition;
    }
}
?>