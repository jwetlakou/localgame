<?php
declare(strict_types=1);
namespace App;

final class Des {
    private ?int $de1 = null;
    private ?int $de2 = null;

    public function roll (): array {

        $this->de1 = random_int(1, 6);
        $this->de2 = random_int(1, 6);
        return $this->toArray();
    }

    public function getValues (): array { return [$this -> de1, $this -> de2]; }

    public function isDouble (): bool {
        if ($this->de1 === null || $this->de2 === null) { return false; }
        return $this->de1 === $this->de2;
    }

    public function containsSix (): bool {
        if ($this->de1 === null || $this->de2 === null) { return false; }
        return $this->de1 === 6 || $this->de2 === 6;
    }

    public function getDiceMax (): ?int {
        if ($this->de1 === null || $this->de2 === null) { return null; }
        return max($this->de1, $this->de2);
    }

    public function getDiceMin (): ?int {
        if ($this->de1 === null || $this->de2 === null) { return null; }
        return min($this->de1, $this->de2);
    }

    public function getDiceSum(): ?int {
        if ($this->de1 === null || $this->de2 === null) { return null; }
        return $this->de1 + $this->de2;
    }

    public function toArray(): array {
        return [
            'des'       => [$this->de1, $this->de2],
            'de1'       => $this->de1,
            'de2'       => $this->de2,
            'somme'     => $this->getDiceSum(),
            'estDouble' => $this->isDouble(),
            'avoirSix'  => $this->containsSix(),
            'deMax'     => $this->getDiceMax(),
            'deMin'     => $this->getDiceMin(),
        ];
    }
}
?>