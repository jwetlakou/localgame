<?php
declare(strict_types=1);
namespace App;

use LogicException;

final class PawnStack {
    private array $pions = [];

    public function push (Pion $pion): void { $this->pions[] = $pion; }

    public function pop (): Pion {
        if ($this->isEmpty()) { throw new LogicException("IMPOSSIBLE DE RETIRE UN PION D'UNE PILE VIDE"); }
        return array_pop($this->pions);
    }

    public function peek (): Pion {
        if ($this->isEmpty()) { throw new LogicException("IMPOSSIBLE DE CONSULTER UNE PILE VIDE"); }
        return $this->pions[array_key_last($this->pions)];
    }

    public function isEmpty (): bool { return $this->pions === []; }

    public function count (): int { return count($this->pions); }

    public function contains (Pion $pion): bool {
        foreach ($this->pions as $pionOfPile) {
            if ($pionOfPile === $pion) { return true; }
        }
        return false;
    }

    public function getPawns (): array { return $this->pions; }

    public function toArray (): array { return array_map( fn(Pion $pion): array => $pion->toArray(), $this->pions); }
}
?>