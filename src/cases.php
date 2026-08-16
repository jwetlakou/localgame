<?php
namespace App;

class Cases {
    public const TYPE_NEUTRE = 'NEUTRE';
    public const TYPE_DEPART = 'DEPART';
    public const TYPE_ARRIVEE = 'ARRIVEE';
    public const TYPE_CENTRE = 'CENTRE';

    public int $id;
    public string $type;
    public ?string $colorSecteur;

    public array $pions = [];

    public function __construct (int $id, string $type = self::TYPE_NEUTRE, ?string $colorSecteur = null) {
        $this->id = $id;
        $this->type = $type;
        $this->colorSecteur = $colorSecteur;
    }

    public function empiler (Pion $pion): void {
        $this->pions[] = $pion;
    }

    public function depiler (): ?Pion {
        if ($this->isEmpty()) {
            return null;
        }
        return array_pop($this->pions);
    }

    public function getPionOnSommet (): ?Pion {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->pions[count($this->pions) - 1];
    }

    public function isEmpty (): bool {
        return empty($this->pions);
    }

    public function nbPions (): int {
        return count($this->pions);
    }

    public function vider (): array {
        $oldPions = $this->pions;
        $this->pions = [];
        return $oldPions;
    }

    public function toArray (): array {
        $pilePions = [];
        foreach ($this->pions as $pion) {
            $pilePions[] = $pion->toArray();
        }
        $pionSommet = $this->getPionOnSommet();

        return [
            'id'            => $this->id,
            'type'          => $this->type,
            'colorSecteur'  => $this->colorSecteur,
            'nbPions'       => $this->nbPions(),
            'pionActif'     => $pionSommet ? $pionSommet->toArray() : null,
            'pilePions'     => $pilePions
        ];
    }
}
?>