<?php
declare(strict_types=1);
namespace App;

use LogicException;

enum TYPE_CASE: string {
    case NEUTRE = 'NEUTRE';
    case DEPART = 'DEPART';
    case ARRIVEE = 'ARRIVEE';
    case CENTRE = 'CENTRE';
}

final class Cases {
    private int $id;
    private TYPE_CASE $type;
    private ?COLOR $secteur;
    private PawnStack $pile;

    public function getId(): int { return $this->id; }
    public function getColorSector (): ?COLOR { return $this->secteur; }
    public function getType (): TYPE_CASE { return $this->type; }
    
    public function __construct (int $id, TYPE_CASE $type = TYPE_CASE::NEUTRE, ?COLOR $secteur = null) {
        if ($id < 0) { throw new LogicException("ID D'UNE CASE NE PEUT PAS ETRE NEGATIF"); }
        $this->id = $id;
        $this->type = $type;
        $this->secteur = $secteur;
        $this->pile = new PawnStack();
        $this->validateConfiguration();
    }

    private function validateConfiguration (): void {
        if ($this->type === TYPE_CASE::DEPART && $this->secteur === null) { throw new LogicException("UNE CASE DEPART DOIT AVOIR UNE COULEUR"); }
        if ($this->type === TYPE_CASE::ARRIVEE && $this->secteur === null) { throw new LogicException("UNE CASE ARRIVÉE DOIT AVOIR UNE COULEUR"); }
        // centre doit avoir une couleur car les pions qui rentrent dans le centre doit appartenir a la couleur du centre.
        if ($this->type === TYPE_CASE::CENTRE && $this->secteur === null) { throw new LogicException("UNE CASE CENTRE DOIT AVOIR UNE COULEUR"); }
        if ($this->type === TYPE_CASE::NEUTRE && $this->secteur !== null) { throw new LogicException("UNE CASE NEUTRE NE PEUT PAS AVOIR DE COULEUR"); }
    }

    public function getTopPawn (): ?Pion {
        if ($this->pile->isEmpty()) { return null; }
        return $this->pile->peek();
    }

    public function isPawnActive (Pion $pion): bool {
        if (!$this->pile->contains($pion)) { return false; }
        return $this->pile->peek() === $pion;
    }

    public function countPawns (): int { return $this->pile->count(); }

    public function addPawns (Pion $pion): void { $this->pile->push($pion); }

    public function removeTopPawn (): ?Pion {
        if ($this->pile->isEmpty()) { return null; }
        return $this->pile->pop();
    }

    public function clear (): array {
        $pions = [];
        while (!$this->pile->isEmpty()) { $pions[] = $this->pile->pop(); }
        return $pions;
    }

    public function containsPawn (Pion $pion): bool { return $this->pile->contains($pion); }

    public function isEmpty (): bool { return $this->pile->isEmpty(); }
    
    public function toArray (): array {
        $topPawn = $this->getTopPawn();
        return [
            'id'          => $this->id,
            'type'        => $this->type->value,
            'secteur'     => $this->secteur->value,
            'nombrePions' => $this->pile->count(),
            'pionActif'   => $topPawn?->toArray(),
            'pions'       => $this->pile->toArray(),
        ];
    }
}
?>