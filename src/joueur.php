<?php
declare(strict_types=1);
namespace App;

final class Joueur {
    public const MAX_PIONS = 4;

    private int $id;
    private string $nom;
    private COLOR $couleur;
    private bool $isBot;

    private array $pions = [];

    public function __construct(int $id, string $nom, COLOR $couleur, bool $isBot = false) {
        if ($id < 0) { throw new \InvalidArgumentException ("ID DU JOUEUR NE PAS ETRE NEGATIF"); }
        $nom = trim($nom);
        if ($nom === '') { throw new \InvalidArgumentException ("NOM DU JOUEUR NE PEUT PAS ETRE VIDE"); }
        $this->id = $id; $this->nom = $nom;
        $this->couleur = $couleur; $this->isBot = $isBot;
        $this->initialiserPions();
    }

    private function initialiserPions (): void {
        for ($i = 1; $i <= self::MAX_PIONS; $i++) { $this->pions[] = new Pion($i, $this->couleur); }
    }

    public function getId (): int { return $this->id; }
    public function getName (): string { return $this->nom; }
    public function getColor (): COLOR { return $this->couleur; }
    public function isBot (): bool { return $this->isBot; }
    
    public function getPionById (int $pionId): ?Pion {
        foreach ($this->pions as $pion) {
            if ($pion->getId() === $pionId) { return $pion; }
        }
        return null;
    }
    
    public function getPionsByEtat (ETAT_PION $etat): array {
        $resultat = [];
        foreach ($this->pions as $pion) {
            if ($pion->getState() === $etat) { $resultat[] = $pion; }
        }
        return $resultat;
    }

    public function isWin (): bool {
        foreach ($this->pions as $pion) {
            if ($pion->getState() !== ETAT_PION::ETAT_CENTRE) { return false; }
        }
        return true;
    }

    public function toArray(): array {
        return [
            'id'      => $this->id,
            'nom'     => $this->nom,
            'couleur' => $this->couleur->value,
            'isBot'   => $this->isBot,
            'pions'   => array_map( static fn (Pion $pion): array => $pion->toArray(), $this->pions)
        ];
    }
}
?>