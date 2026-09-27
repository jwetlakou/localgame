<?php
declare(strict_types=1);
namespace App;

use LogicException;
use InvalidArgumentException;

enum COLOR: string {
    case ROUGE = 'ROUGE';
    case BLEU = 'BLEU';
    case JAUNE = 'JAUNE';
    case VERT = 'VERT';
}

enum ETAT_PION: string {
    case ETAT_MAISON = 'MAISON';
    case ETAT_CIRCUIT = 'CIRCUIT';
    case ETAT_OTAGE = 'OTAGE';
    case ETAT_ARRIVEE = 'ARRIVEE';
    case ETAT_CENTRE = 'CENTRE';
}

class Pion {
    private const POSITION_MAISON = -1;

    private const CIRCUIT_MIN = 0;
    private const CIRCUIT_MAX = 51;

    private const ARRIVEE_MIN = 52;
    private const ARRIVEE_MAX = 56;

    private const POSITION_CENTRE = 57;

    private int $id;
    private COLOR $couleur;
    private ETAT_PION $etat;
    private int $position;

    private ?COLOR $detenuPar = null;

    public function getId (): int { return $this->id; }
    public function getColor (): COLOR { return $this->couleur; }
    public function getPosition (): int { return $this->position; }
    public function getState (): ETAT_PION { return $this->etat; }
    public function getDetenuPar (): ?COLOR { return $this->detenuPar; }
    
    public function __construct(int $id, COLOR $couleur) {
        if ($id <= 0) { throw new InvalidArgumentException("ID D'UN PION DOIT ÊTRE STRICTEMENT POSITIF"); }
        $this->id = $id; $this->couleur = $couleur;
        $this->position = self::POSITION_MAISON; $this->etat = ETAT_PION::ETAT_MAISON;
        $this->detenuPar = null;
        $this->assertInvariant();
    }

    private function assertInvariant(): void {
        switch ($this->etat) {
            case ETAT_PION::ETAT_MAISON:
                if ($this->position !== self::POSITION_MAISON) { throw new LogicException("INVARIANT VIOLÉ - UN PION EN MAISON DOIT AVOIR POSITION -1"); }
                if ($this->detenuPar !== null) { throw new LogicException("INVARIANT VIOLÉ - UN PION EN MAISON NE PEUT PAS AVOIR DE DETENTEUR"); }
                break;
            case ETAT_PION::ETAT_CIRCUIT:
                if ($this->position < self::CIRCUIT_MIN || $this->position > self::CIRCUIT_MAX) { throw new LogicException("INVARIANT VIOLÉ - POSITION DU PION SUR LE CIRCUIT DOIT ÊTRE COMPRISE ENTRE 0 ET 51"); }
                if ($this->detenuPar !== null) { throw new LogicException("INVARIANT VIOLÉ - UN PION SUR LE CIRCUIT NE PEUT PAS AVOIR DE DETENTEUR"); }
                break;
            case ETAT_PION::ETAT_OTAGE:
                if ($this->position !== self::POSITION_MAISON) { throw new LogicException("INVARIANT VIOLÉ - UN PION EN MAISON DOIT AVOIR POSITION -1"); }
                if ($this->detenuPar === null) { throw new LogicException("INVARIANT VIOLÉ - UN PION EN MAISON DOIT AVOIR UN DETENTEUR"); }
                if ($this->detenuPar === $this->couleur) { throw new LogicException("INVARIANT VIOLÉ - UN PION NE PEUT PAS ÊTRE DETENU PAR SA PROPRE COULEUR"); }
                break;
            case ETAT_PION::ETAT_ARRIVEE:
                if ($this->position < self::ARRIVEE_MIN || $this->position > self::ARRIVEE_MAX) { throw new LogicException("INVARIANT VIOLÉ - POSITION DU PION EN ARRIVÉE DOIT ÊTRE COMPRISE ENTRE 52 ET 56"); }
                if ($this->detenuPar !== null) { throw new LogicException("INVARIANT VIOLÉ - UN PION EN ARRIVÉE NE PEUT PAS AVOIR DE DETENTEUR"); }
                break;
            case ETAT_PION::ETAT_CENTRE:
                if ($this->position !== self::POSITION_CENTRE) { throw new LogicException("INVARIANT VIOLÉ - UN PION AU CENTRE DOIT AVOIR LA POSITION 57"); }
                if ($this->detenuPar !== null) { throw new LogicException("INVARIANT VIOLÉ - UN PION AU CENTRE NE PEUT PAS AVOIR DE DETENTEUR"); }
                break;
        }
    }

    public function takenBy (COLOR $couleur): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT) { throw new \LogicException("SEUL UN PION SUR LE CIRCUIT PEUT ÊTRE CAPTURÉ"); }
        if ($couleur === $this->couleur) { throw new LogicException("UN PION NE PEUT PAS ÊTRE CAPTURÉ PAR UN PION DE SA PROPRE COULEUR"); }

        $this->etat = ETAT_PION::ETAT_OTAGE; $this->position = self::POSITION_MAISON;
        $this->detenuPar = $couleur;
        
        $this->assertInvariant();
    }

    public function release (): void {
        if ($this->etat !== ETAT_PION::ETAT_OTAGE) { throw new LogicException("SEUL UN PION EN OTAGE PEUT ÊTRE LIBÉRÉ"); }

        $this->etat = ETAT_PION::ETAT_MAISON; $this->position = self::POSITION_MAISON;
        $this->detenuPar = null;
        
        $this->assertInvariant();
    }

    public function putOnStart(int $position): void {
        if ($this->etat !== ETAT_PION::ETAT_MAISON) { throw new LogicException("SEUL PION EN MAISON PEUT ENTRER SUR CIRCUIT"); }
        if ($position < self::CIRCUIT_MIN || $position > self::CIRCUIT_MAX) {throw new InvalidArgumentException("POSITION DU CIRCUIT DOIT ÊTRE COMPRISE ENTRE 0 ET 51");}

        $this->etat = ETAT_PION::ETAT_CIRCUIT; $this->position = $position;
        $this->detenuPar = null;

        $this->assertInvariant();
    }

    public function startZone (): void {
        if ($this->etat !== ETAT_PION::ETAT_MAISON) { throw new LogicException("SEUL UN PION EN MAISON PEUT ENTRER SUR LE CIRCUIT"); }

        $this->etat = ETAT_PION::ETAT_CIRCUIT; $this->position = self::CIRCUIT_MIN;
        $this->detenuPar = null;

        $this->assertInvariant();
    }

    public function moveTo (int $position): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT && $this->etat !== ETAT_PION::ETAT_ARRIVEE) { throw new LogicException("SEUL UN PION SUR LE TRAJET PEUT SE DEPLACER"); }
        
        if ($this->etat === ETAT_PION::ETAT_CIRCUIT) {
            if ($position < self::CIRCUIT_MIN || $position > self::POSITION_CENTRE) { throw new InvalidArgumentException("SUR LE CIRCUIT, LA POSITION DOIT ÊTRE COMPRISE ENTRE 0 ET 57."); }
            $this->position = $position;

            if ($position === self::POSITION_CENTRE) { $this->etat = ETAT_PION::ETAT_CENTRE; }
            elseif ($position >= self::ARRIVEE_MIN) { $this->etat = ETAT_PION::ETAT_ARRIVEE; }
            else { $this->etat = ETAT_PION::ETAT_CIRCUIT; }
        } else {
            if ($position < self::ARRIVEE_MIN || $position > self::POSITION_CENTRE) { throw new InvalidArgumentException("PION DANS L'ARRIVÉE NE PEUT AVANCER QUE VERS POSITION COMPRISE ENTRE 52 ET 57"); }
            $this->position = $position;

            if ($position === self::POSITION_CENTRE) { $this->etat = ETAT_PION::ETAT_CENTRE; }
            else { $this->etat = ETAT_PION::ETAT_ARRIVEE; }
        }
        $this->assertInvariant();
    }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'couleur'   => $this->couleur->value,
            'etat'      => $this->etat->value,
            'position'  => $this->position,
            'detenuPar' => $this->detenuPar?->value
        ];
    }
}
?>