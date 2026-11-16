<?php
declare(strict_types=1);
namespace App;

use LogicException;

final class GestionnaireTour {
    private ?Joueur $joueurActif = null;
    private ?array $diceValues = null;
    private array $combinaisons = [];
    private bool $tourEnCours = false;
    private bool $relanceAccordee = false;

    public function __construct (private readonly Des $des, private readonly Combinaison $evaluateur) {}
    
    public function demarrerTour (Joueur $joueur, Plateau $plateau): array {
        if ($this->tourEnCours) { throw new LogicException("UN TOUR EST DEJA EN COURS"); }

        $diceResults = $this->des->roll();
        $valeurs = $this->des->getValues();

        if (count($valeurs) !== 2 || !is_int($valeurs[0]) || !is_int($valeurs[1])) { throw new LogicException("RESULTA DES DES EST INCOHERENT"); }
        
        $this->joueurActif = $joueur;
        $this->diceValues = [$valeurs[0], $valeurs[1]];
        $this->tourEnCours = true;
        $this->relanceAccordee = $this->des->containsSix();

        $evaluation = $this->evaluateur->evaluate($joueur, $this->diceValues, $plateau);

        $this->combinaisons = $evaluation;

        return [
            'joueur' => [
                'id' => $joueur->getId(),
                'nom' => $joueur->getName(),
                'couleur' => $joueur->getColor()->value,
            ],
            'des' => $diceResults,
            'evaluation' => $evaluation,
            'relanceAccordee' => $this->relanceAccordee,
        ];
    }

    public function getJoueurActif (): ?Joueur {
        return $this->joueurActif;
    }

    public function getDiceValues (): ?array {
        return $this->diceValues;
    }

    public function isTourEnCours (): bool {
        return $this->tourEnCours;
    }

    public function isRelanceAccordee (): bool {
        return $this->relanceAccordee;
    }

    public function terminerTour (): void {
        if (!$this->tourEnCours) { throw new LogicException("AUCUN TOUR N'EST EN COURS"); }

        $this->joueurActif = null;
        $this->diceValues = null;
        $this->combinaisons = [];
        $this->tourEnCours = false;
        $this->relanceAccordee = false;
    }
}
?>