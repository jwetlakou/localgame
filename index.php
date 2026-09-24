<?php
declare(strict_types=1);
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/pions.php';

// use App\Plateau;
// use App\Cases;
// use App\Pion;
// use App\PawnStack;
// use App\Des;
use App\COLOR;
use App\Joueur;
// use App\TYPE_CASE;
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






























// $pion = new Pion(1, COLOR::ROUGE);
// $joueur = new Joueur(1, "Munden", COLOR::ROUGE);
// $pile = new PawnStack();
// $case = new Cases(1, TYPE_CASE::NEUTRE);

// $pion->startZone();
// $pion->moveTo(1);
// $pile->push($pion);

// echo "* ID : {$joueur->getId()}<br/>* NOM : {$joueur->getName()}<br/>* COULEUR : {$joueur->getColor()->value}<br/><br/>";
// echo "* ID : {$pion->getId()}<br/>* couleur : {$pion->getColor()->value}<br/>* état : {$pion->getState()->value}<br/>* position : {$pion->getPosition()}<br/>* détenu par : {$pion->getDetenuPar()->value}<br/><br/>";
// echo "* PG : {$plateau->positionToPG($pion->getPosition(), $pion->getColor())}<br/><br/>";
// echo "* ID : {$case->getId()}<br/>* secteur : {$case->getColorSector()->value}<br/>* type : {$case->getType()->value}<br/>* nombre de pions : {$case->countPawns()}<br/>* pion actif : {$case->getTopPawn()}<br/>";






// $rouge = $plateau->getIndexDepart(COLOR::ROUGE);
// $bleu = $plateau->getIndexDepart(COLOR::BLEU);
// $jaune = $plateau->getIndexDepart(COLOR::JAUNE);
// $vert = $plateau->getIndexDepart(COLOR::VERT);

// echo "position depart rouge sur plateau => {$rouge}<br/>";
// echo "position depart bleu sur plateau => {$bleu}<br/>";
// echo "position depart jaune sur plateau => {$jaune}<br/>";
// echo "position depart vert sur plateau => {$vert}<br/>";

// echo "nombre de case : ". count($plateau->getCircuit()) ."";
// function test (string $nom, bool $condition): void {
//     if ($condition) {
//         echo "✅ {$nom} - vrai<br/>";
//     } else {
//         echo "❌ {$nom} - faux<br/>";
//     }
// }
// foreach (COLOR::cases() as $couleur) {
//     for ($irg = 0; $irg < 52; $irg++) {
//         $pg = $plateau->positionToPG($irg, $couleur); // IRG -> PG
//         $pos = $plateau->PGToPosition($pg, $couleur); // PG -> IRG

//         if ($pos !== $irg) { echo "❌ {$couleur->value} - IRG {$irg} → PG {$pg} → IRG {$pos}<br>"; }
//         test("{$couleur->value} : bonne position", true);
//     }
// }

// test("aucune case manquante ?", count($plateau->getCircuit()) === 52);
// for ($i = 0; $i < 52; $i++) {
//     $circuit = $plateau->getCircuit();
//     if (!isset($circuit[$i])) {
//         echo "erreur - il manque la case #{$i}";
//     }
// }





// function texception (string $nom, callable $fonction, string $exc = LogicException::class): void {
//     try {
//         $fonction();
//         echo "{$nom} - aucune {$exc}";
//     } catch (Throwable $e) {
//         if ($e instanceof $exc) {
//             echo "{$nom} - {$e->getMessage()}";
//         } else { echo "{$nom} - mauvaise exception"; }
//     }
// }
// texception("comportement refusé", fn() => $case1 = new Cases(1, TYPE_CASE::DEPART));



































// =====================================
// $bluePion2 = new Pion(2, COLOR::BLEU); 
// echo "pion créé : pion {$bluePion2->getColor()->value} #{$bluePion2->getId()}<br/>";



echo "<br/>";
// =====================================================
// $plateau = new Plateau();

// $circuit = $plateau->getCircuit();
// echo "circuit : ". count($circuit) ." cases<br/><br/>";

// $caseDepartRouge = $plateau->getCase(0)->getId();
// echo "numero case : ". $caseDepartRouge ."<br/><br/>";

// $departrouge = $plateau->getIndexDepart(COLOR::ROUGE);
// echo "numero depart rouge : ". $departrouge ."<br/>";
// $departbleu = $plateau->getIndexDepart(COLOR::BLEU);
// echo "numero depart bleu : ". $departbleu ."<br/>";

// $departjaune = $plateau->getIndexDepart(COLOR::JAUNE);
// echo "numero depart jaune : ". $departjaune ."<br/>";
// $departvert = $plateau->getIndexDepart(COLOR::VERT);
// echo "numero depart vert : ". $departvert ."<br/><br/>";

// $positionRouge = $plateau->positionToPG(0, COLOR::ROUGE);
// echo "position depart rouge sur plateau : ". $positionRouge ."<br/>";
// $positionBleu = $plateau->positionToPG(0, COLOR::BLEU);
// echo "position depart bleu sur plateau : ". $positionBleu ."<br/>";
// $positionJaune = $plateau->positionToPG(0, COLOR::JAUNE);
// echo "position depart jaune sur plateau : ". $positionJaune ."<br/>";
// $positionVert = $plateau->positionToPG(0, COLOR::VERT);
// echo "position depart vert sur plateau : ". $positionVert ."<br/><br/>";

// $pionPlateauRouge = $plateau->PGToPosition(0, COLOR::ROUGE);
// echo "position rouge dans son circuit : ". $pionPlateauRouge ."<br/>";
// $pionPlateauBleu = $plateau->PGToPosition(0, COLOR::BLEU);
// echo "position bleu dans son circuit : ". $pionPlateauBleu ."<br/>";
// $pionPlateauJaune = $plateau->PGToPosition(0, COLOR::JAUNE);
// echo "position jaune dans son circuit : ". $pionPlateauJaune ."<br/>";
// $pionPlateauVert = $plateau->PGToPosition(0, COLOR::VERT);
// echo "position vert dans son circuit : ". $pionPlateauVert ."<br/><br/>";

// $nextPositionRouge = $plateau->calculateNextPosition(0, 4);
// echo "nouveau position rouge dans le circuit : ". $nextPositionRouge ."<br/>";
// $nextPositionBleu = $plateau->calculateNextPosition(0, 6);
// echo "nouveau position bleu dans le circuit : ". $nextPositionBleu ."<br/>";
// $nextPositionJaune = $plateau->calculateNextPosition(0, 8);
// echo "nouveau position jaune dans le circuit : ". $nextPositionJaune ."<br/>";
// $nextPositionVert = $plateau->calculateNextPosition(0, 10);
// echo "nouveau position vert dans le circuit : ". $nextPositionVert ."<br/><br/>";

// echo "test coherence de 52 positions<br/>";
// foreach (COLOR::cases() as $couleur) {
//     echo "<h4>{$couleur->value}</h4>";

//     echo '<table border="2" cellpadding="5">';
//     echo '<tr>';
//     echo '<th>position pion</th>';
//     echo '<th>position plateau</th>';
//     echo '</tr>';

//     for ($irg = 0; $irg < 52; $irg++) {
//         $pg = $plateau->positionToPG($irg, $couleur);

//         echo '<tr>';
//         echo "<td>{$irg}</td>";
//         echo "<td>{$pg}</td>";
//         echo '</tr>';
//     }
//     echo "</table>";
// }
?>