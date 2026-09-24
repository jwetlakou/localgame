<?php
declare(strict_types=1);
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/pions.php';

use App\COLOR;
use App\Joueur;
use App\Plateau;

$joueur = new Joueur(1, "Munden", COLOR::JAUNE);
echo 'info du joueur : <br/>
<table border="2">
    <tr>
        <th>nom</th>
        <td>'.$joueur->getName().'</td>
    </tr>
    <tr>
        <th>couleur</th>
        <td>'.$joueur->getColor()->value.'</td>
    </tr>
    <tr>
        <th>ID</th>
        <td>#'.$joueur->getId().'</td>
    </tr>
</table><br/>';

$plateau = new Plateau();
$pion = $joueur->getPionById(1);
if ($pion === null) { throw new RuntimeException("pion est introuvable."); }
echo 'info du pion : <br/>
<table border="2">
    <tr>
        <th>ID</th>
        <td>#'.$pion->getId().'</td>
    </tr>
    <tr>
        <th>couleur</th>
        <td>'.$pion->getColor()->value.'</td>
    </tr>
    <tr>
        <th>position</th>
        <td>'.$pion->getPosition().'</td>
    </tr>
    <tr>
        <th>état</th>
        <td>'.$pion->getState()->value.'</td>
    </tr>
    <tr>
        <th>détenu par</th>
        <td>'.$pion->getDetenuPar()->value.'</td>
    </tr>
</table><br/>';

$pion->startZone();
$pion->moveTo(52);
echo 'info du pion : <br/>
<table border="2">
    <tr>
        <th>ID</th>
        <td>#'.$pion->getId().'</td>
    </tr>
    <tr>
        <th>couleur</th>
        <td>'.$pion->getColor()->value.'</td>
    </tr>
    <tr>
        <th>position</th>
        <td>'.$pion->getPosition().'</td>
    </tr>
    <tr>
        <th>état</th>
        <td>'.$pion->getState()->value.'</td>
    </tr>
    <tr>
        <th>état</th>
        <td>'.$pion->getDetenuPar()->value.'</td>
    </tr>
</table><br/>';

$irg = $pion->getPosition();
echo "position générale du pion : ". $irg ."<br/>";
$pg = $plateau->positionToPG($irg, $pion->getColor());
echo "position au plateau : ". $pg ."<br/>";

$case = $plateau->getCase($pg);
echo "numero case : ". $case->getId() ."<br/>";

$case->addPawns($pion);
echo "nombre de pions dans la case : ".$case->countPawns() ."<br/>";

$pionActif = $case->getTopPawn();

if ($pionActif === $pion) {
    echo "pion du joueur est bien actif";
} else {
    echo "pion actif ne corresponds pas";
}
?>