<?php
namespace App;

use App\Pion;

class Joueur {
    public int $id;
    public string $nom;
    public string $couleur;
    public bool $isBot;

    public array $pions = [];

    public function __construct(int $id, string $nom, string $couleur, bool $isBot = false) {
        $this->id = $id;
        $this->nom = $nom;
        $this->couleur = $couleur;
        $this->isBot = $isBot;
        $this->initialiserPions();
    }

    private function initialiserPions (): void {
        for ($i = 1; $i <= 4; $i++) { $this->pions[] = new Pion($i, $this->couleur); }
    }

    public function getPionById (int $id): ?Pion {
        foreach ($this->pions as $pion) {
            if ($pion->id === $id) { return $pion; }
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
        $pionsArray = [];
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