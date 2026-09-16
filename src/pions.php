<?php
namespace App;

use LogicException;

enum ETAT_PION: string {
    case ETAT_MAISON = 'MAISON';
    case ETAT_CIRCUIT = 'CIRCUIT';
    case ETAT_OTAGE = 'OTAGE';
    case ETAT_ARRIVEE = 'ARRIVEE';
    case ETAT_CENTRE = 'CENTRE';
}

class Pion {
    private int $id;
    private string $couleur;
    private ETAT_PION $etat;
    private int $position;
    private ?string $detenuPar = null;

    public function getId (): int { return $this->id; }
    public function getColor (): string { return $this->couleur; }
    public function getPosition (): int { return $this->position; }
    public function getState (): ETAT_PION { return $this->etat; }
    public function getDetenuPar (): ?string { return $this->detenuPar; }
    
    public function __construct(int $id, string $couleur) {
        $this->id = $id; $this->couleur = $couleur;
        $this->position = -1; $this->etat = ETAT_PION::ETAT_MAISON;
        // $this->detenuPar = null;
    }

    public function takenBy (string $couleur): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT) { throw new \LogicException("SEUL UN PION SUR LE CIRCUIT PEUT ETRE CAPTURÉ"); }
        
        if ($couleur === $this->couleur) { throw new \Exception("UN PION NE PEUT PAS ETRE CAPTURÉ PAR UN PION DE SA PROPRE COULEUR"); }
        
        $this->etat = ETAT_PION::ETAT_OTAGE;
        $this->position = -1;
        $this->detenuPar = $couleur;
    }

    public function release (): void {
        if ($this->etat !== ETAT_PION::ETAT_OTAGE) { throw new \LogicException("SEUL UN PION EN OTAGE PEUT ETRE LIBÉRÉ"); }

        $this->etat = ETAT_PION::ETAT_MAISON;
        $this->position = -1;
        $this->detenuPar = null;
    }

    public function startZone (): void {
        if ($this->etat !== ETAT_PION::ETAT_MAISON) { throw new \LogicException("SEUL UN PION EN MAISON PEUT ENTRER SUR LE CIRCUIT"); }

        $this->etat = ETAT_PION::ETAT_CIRCUIT;
        $this->position = 0;
    }

    public function moveTo (int $position): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT) { throw new \LogicException("SEUL UN PION SUR LE CIRCUIT PEUT SE DEPLACER"); }
        if ($position < 0 || $position > 57) { throw new LogicException("LA POSITION DOIT ETRE COMPRISE ENTRE 0 ET 57."); }
        $this->position = $position;

        if ($position >= 52 && $position <= 56) { $this->etat = ETAT_PION::ETAT_ARRIVEE; }
        if ($position === 57) { $this->etat = ETAT_PION::ETAT_CENTRE; }
    }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'couleur'   => $this->couleur,
            'etat'      => $this->etat->value,
            'position'  => $this->position,
            'detenuPar' => $this->detenuPar
        ];
    }
}
?>