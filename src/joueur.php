<?php
namespace App;

use App\Pion;

class Joueur {
    public const MAX_PIONS = 4;

    public int $id;
    public string $nom;
    public string $couleur;
    public bool $isBot;

    private array $pions = [];

    public function __construct(int $id, string $nom, string $couleur, bool $isBot = false) {
        $this->id = $id;
        $this->nom = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8');
        $this->couleur = htmlspecialchars(trim($couleur), ENT_QUOTES, 'UTF-8');
        $this->isBot = $isBot;
        $this->initialiserPions();
    }

    private function initialiserPions (): void {
        for ($i = 1; $i <= self::MAX_PIONS; $i++) { $this->pions[] = new Pion($i, $this->couleur); }
    }

    public function addPion (Pion $pion): void {
        if (count($this->pions) >= self::MAX_PIONS) { throw new \LogicException("REGLE VIOLÉE - un joueur peut pas avoir plus de " . self::MAX_PIONS . " pions."); }
        $this->pions[] = $pion;
    }

    public function getPions (): array { return $this->pions; }

    public function getPionById (int $pionId): ?Pion {
        foreach ($this->pions as $pion) {
            if ($pion->id === $pionId) { return $pion; }
        }
        return null;
    }

    public function getPionsByEtat (string $etat): array {
        $resultat = [];
        foreach ($this->pions as $pion) {
            if ($pion->etat === $etat) { $resultat[] = $pion; }
        }
        return $resultat;
    }

    public function isWin (): bool {
        foreach ($this->pions as $pion) {
            if ($pion->etat !== Pion::ETAT_CENTRE) { return false; }
        }
        return true;
    }

    public function toArray(): array {
        $pionsArray = array_map(function (Pion $pion) { return $pion->toArray(); }, $this->pions);
        foreach ($this->pions as $pion) { $pionsArray[] = $pion->toArray(); }

        return [
            'id'        => $this->id,
            'nom'        => $this->nom,
            'couleur'   => $this->couleur,
            'isBot'     => $this->isBot,
            'pions'     => $pionsArray,
        ];
    }
}
?>