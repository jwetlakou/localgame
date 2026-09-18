<?php
declare(strict_types=1);
namespace App;

use LogicException;

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
        $this->id = $id; $this->couleur = $couleur;
        $this->position = -1; $this->etat = ETAT_PION::ETAT_MAISON;
        // $this->detenuPar = null;
    }

    public function takenBy (COLOR $couleur): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT) { throw new \LogicException("SEUL UN PION SUR LE CIRCUIT PEUT ETRE CAPTURÉ"); }
        
        if ($couleur === $this->couleur) { throw new \LogicException("UN PION NE PEUT PAS ETRE CAPTURÉ PAR UN PION DE SA PROPRE COULEUR"); }
        
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

    public function entrerSurCircuit(int $positionDepart): void {
        # Un pion ne peut entrer sur le circuit que depuis la maison.
        if ($this->etat !== ETAT_PION::ETAT_MAISON) {
            throw new \LogicException(
                'Seul un pion en maison peut entrer sur le circuit.'
            );
        }

        # Le circuit logique possède 52 positions : 0 à 51.
        if ($positionDepart < 0 || $positionDepart > 51) {
            throw new \InvalidArgumentException(
                'La position de départ doit être comprise entre 0 et 51.'
            );
        }

        # Le pion quitte la maison et prend sa position réelle
        # sur le circuit global.
        $this->etat = ETAT_PION::ETAT_CIRCUIT;
        $this->position = $positionDepart;
    }

    public function startZone (): void {
        if ($this->etat !== ETAT_PION::ETAT_MAISON) { throw new \LogicException("SEUL UN PION EN MAISON PEUT ENTRER SUR LE CIRCUIT"); }

        $this->etat = ETAT_PION::ETAT_CIRCUIT;
        $this->position = 0;
    }

    public function moveTo (int $position): void {
        if ($this->etat !== ETAT_PION::ETAT_CIRCUIT && $this->etat !== ETAT_PION::ETAT_ARRIVEE) { throw new \LogicException("SEUL UN PION SUR LE TRAJET PEUT SE DEPLACER"); }
        
        if ($position < 0 || $position > 57) { throw new LogicException("LA POSITION DOIT ETRE COMPRISE ENTRE 0 ET 57."); }
        $this->position = $position;

        if ($position >= 52 && $position <= 56) { $this->etat = ETAT_PION::ETAT_ARRIVEE; return; }
        if ($position === 57) { $this->etat = ETAT_PION::ETAT_CENTRE; return; }

        $this->etat = ETAT_PION::ETAT_CIRCUIT;
    }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'couleur'   => $this->couleur->value,
            'etat'      => $this->etat->value,
            'position'  => $this->position,
            'detenuPar' => $this->detenuPar
        ];
    }
}
?>