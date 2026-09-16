<?php
namespace App;

class Des {
    public int $de1;
    public int $de2;

    public function __construct () { $this->lancer(); }

    public function lancer (): array {
        $this->de1 = random_int(1, 6);
        $this->de2 = random_int(1, 6);

        return [ 'de1' => $this->de1, 'de2' => $this->de2, 'isDouble' => $this->isDouble() ];
        // return $this->toArray();
    }

    public function getValues (): array { return [$this -> de1, $this -> de2]; }

    public function isDouble (): bool { return $this->de1 > 0 && $this->de1 === $this->de2; }

    public function containsSix (): bool { return $this->de1 === 6 || $this->de2 === 6; }

    public function getDeMax (): int { return max($this->de1, $this->de2); }

    public function getDeMin (): int { return min($this->de1, $this->de2); }

    public function getDeSomme(): int { return $this->de1 + $this->de2; }

    public function toArray(): array {
        return [
            'des'           => [$this->de1, $this->de2],
            'de1'           => $this->de1,
            'de2'           => $this->de2,
            'somme'         => $this->getDeSomme(),
            'isDouble'      => $this->isDouble(),
            'containsSix'   => $this->containsSix(),
            'deMax'         => $this->getDeMax(),
            'deMin'         => $this->getDeMin(),
        ];
    }
}
?>