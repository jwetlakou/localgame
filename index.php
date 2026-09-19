<?php
declare(strict_types=1);
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/pions.php';

use App\Plateau;
use App\Cases;
use App\Pion;
use App\PawnStack;
// use App\Des;
use App\COLOR;

// TEST DE PION ========================
$redPion1 = new Pion(1, COLOR::ROUGE); 
echo "pion créé : pion {$redPion1->getColor()->value} #{$redPion1->getId()}<br/>";
$bluePion2 = new Pion(2, COLOR::BLEU); 
echo "pion créé : pion {$bluePion2->getColor()->value} #{$bluePion2->getId()}<br/>";



echo "<br/>";
// =====================================================
$plateau = new Plateau();

$circuit = $plateau->getCircuit();
echo "circuit : ". count($circuit) ." cases<br/><br/>";

$caseDepartRouge = $plateau->getCase(0)->getId();
echo "numero case : ". $caseDepartRouge ."<br/><br/>";

$departrouge = $plateau->getIndexDepart(COLOR::ROUGE);
echo "numero depart rouge : ". $departrouge ."<br/>";
$departbleu = $plateau->getIndexDepart(COLOR::BLEU);
echo "numero depart bleu : ". $departbleu ."<br/>";

$departjaune = $plateau->getIndexDepart(COLOR::JAUNE);
echo "numero depart jaune : ". $departjaune ."<br/>";
$departvert = $plateau->getIndexDepart(COLOR::VERT);
echo "numero depart vert : ". $departvert ."<br/><br/>";

$positionRouge = $plateau->positionToPG(0, COLOR::ROUGE);
echo "position depart rouge sur plateau : ". $positionRouge ."<br/>";
$positionBleu = $plateau->positionToPG(0, COLOR::BLEU);
echo "position depart bleu sur plateau : ". $positionBleu ."<br/>";
$positionJaune = $plateau->positionToPG(0, COLOR::JAUNE);
echo "position depart jaune sur plateau : ". $positionJaune ."<br/>";
$positionVert = $plateau->positionToPG(0, COLOR::VERT);
echo "position depart vert sur plateau : ". $positionVert ."<br/><br/>";

$pionPlateauRouge = $plateau->PGToPosition(0, COLOR::ROUGE);
echo "position rouge dans son circuit : ". $pionPlateauRouge ."<br/>";
$pionPlateauBleu = $plateau->PGToPosition(0, COLOR::BLEU);
echo "position bleu dans son circuit : ". $pionPlateauBleu ."<br/>";
$pionPlateauJaune = $plateau->PGToPosition(0, COLOR::JAUNE);
echo "position jaune dans son circuit : ". $pionPlateauJaune ."<br/>";
$pionPlateauVert = $plateau->PGToPosition(0, COLOR::VERT);
echo "position vert dans son circuit : ". $pionPlateauVert ."<br/><br/>";

$nextPositionRouge = $plateau->calculateNextPosition(0, 4);
echo "nouveau position rouge dans le circuit : ". $nextPositionRouge ."<br/>";
$nextPositionBleu = $plateau->calculateNextPosition(0, 6);
echo "nouveau position bleu dans le circuit : ". $nextPositionBleu ."<br/>";
$nextPositionJaune = $plateau->calculateNextPosition(0, 8);
echo "nouveau position jaune dans le circuit : ". $nextPositionJaune ."<br/>";
$nextPositionVert = $plateau->calculateNextPosition(0, 10);
echo "nouveau position vert dans le circuit : ". $nextPositionVert ."<br/><br/>";

echo "test coherence de 52 positions<br/>";
foreach (COLOR::cases() as $couleur) {
    echo "<h4>{$couleur->value}</h4>";

    echo '<table border="2" cellpadding="5">';
    echo '<tr>';
    echo '<th>position pion</th>';
    echo '<th>position plateau</th>';
    echo '</tr>';

    for ($irg = 0; $irg < 52; $irg++) {
        $pg = $plateau->positionToPG($irg, $couleur);

        echo '<tr>';
        echo "<td>{$irg}</td>";
        echo "<td>{$pg}</td>";
        echo '</tr>';
    }
    echo "</table>";
}
?>