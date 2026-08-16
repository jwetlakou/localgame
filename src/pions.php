<?php
namespace App;

class Pion {
    public const ETAT_MAISON = 'MAISON';
    public const ETAT_CIRCUIT = 'CIRCUIT';
    public const ETAT_OTAGE = 'OTAGE';
    public const ETAT_ARRIVEE = 'ARRIVEE';
    public const ETAT_CENTRE = 'CENTRE';

    public int $id;
    public string $couleur;
    public string $etat;
    public int $position;
    public ?string $detenuPar;

    public function __construct(int $id, string $couleur) {
        $this -> id = $id;
        $this -> couleur = $couleur;
        $this -> etat = self::ETAT_MAISON;
        $this -> position = -1;
        $this -> detenuPar = null;
    }

    public function capturer (string $couleurAdversaire): void {
        $this->etat = self::ETAT_OTAGE;
        $this->position = -1;
        $this->detenuPar = $couleurAdversaire;
    }

    public function liberer (): void {
        $this->etat = self::ETAT_MAISON;
        $this->position = -1;
        $this->detenuPar = null;
    }

    public function deplacerVers (int $nouvellePosition, string $nouvelEtat = self::ETAT_CIRCUIT): void {
        $this->position = $nouvellePosition;
        $this->etat = $nouvelEtat;
        $this->detenuPar = null;
    }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'couleur'   => $this->couleur,
            'etat'      => $this->etat,
            'position'  => $this->position,
            'detenuPar' => $this->detenuPar,
        ];
    }
}
?>