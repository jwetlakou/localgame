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

        for ($i = 1; $i <= 4; $i++) {
            $this->pions[$i] = new Pion($i, $this->couleur);
        }
    }

    public function getPionById (int $pionId): ?Pion {
        return $this->pions[$pionId] ?? null;
    }

    public function getPionsByEtat (string $etat): array {
        $resultat = [];

        foreach ($this->pions as $pion) {
            if ($pion->etat === $etat) {
                $resultat[] = $pion;
            }
        }
        return $resultat;
    }

    public function isWin (): bool {
        foreach ($this->pions as $pion) {
            if ($pion->etat !== Pion::ETAT_CENTRE) {
                return false;
            }
        }
        return true;
    }

    public function toArray(): array {
        $pionsArray = [];
        
        foreach ($this->pions as $pion) {
            $pionsArray[] = $pion->toArray();
        }

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