<?php

declare(strict_types=1);

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/plateau.php';

use App\COLOR;
use App\Pion;
use App\ETAT_PION;
use App\Joueur;
use App\Plateau;

$plateau = new Plateau();

$redPion = new Pion(1, COLOR::ROUGE);
$bluePion = new Pion(2, COLOR::BLEU);
$yellowPion = new Pion(3, COLOR::JAUNE);
$greenPion = new Pion(4, COLOR::VERT);

$redPion->entrerSurCircuit(
    $plateau->getIndexDepart($redPion->getColor())
);

$bluePion->entrerSurCircuit(
    $plateau->getIndexDepart($bluePion->getColor())
);

$yellowPion->entrerSurCircuit(
    $plateau->getIndexDepart($yellowPion->getColor())
);

$greenPion->entrerSurCircuit(
    $plateau->getIndexDepart($greenPion->getColor())
);

var_dump($redPion->toArray());
echo "<br/><br/>";
var_dump($bluePion->toArray());
echo "<br/><br/>";
var_dump($yellowPion->toArray());
echo "<br/><br/>";
var_dump($greenPion->toArray());
?>