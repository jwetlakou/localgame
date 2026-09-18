<?php

declare(strict_types=1);

require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/pions.php';


use App\Plateau;
use App\COLOR;


/*
|--------------------------------------------------------------------------
| Outils de test
|--------------------------------------------------------------------------
*/

function test(string $nom, bool $condition): void
{
    if ($condition) {
        echo "✅ PASS : {$nom}<br>";
    } else {
        echo "❌ FAIL : {$nom}<br>";
    }
}


/*
|--------------------------------------------------------------------------
| Création du plateau
|--------------------------------------------------------------------------
*/

$plateau = new Plateau();

echo '<h1>TESTS DE LA CLASSE PLATEAU</h1>';


/*
|--------------------------------------------------------------------------
| TEST 1 — Le circuit possède 52 cases
|--------------------------------------------------------------------------
*/

$circuit = $plateau->getCircuit();

test(
    'Le circuit contient exactement 52 cases',
    count($circuit) === 52
);


/*
|--------------------------------------------------------------------------
| TEST 2 — PG commence à 0
|--------------------------------------------------------------------------
*/

test(
    'La première case possède PG = 0',
    $plateau->getCase(0)->getId() === 0
);


/*
|--------------------------------------------------------------------------
| TEST 3 — PG se termine à 51
|--------------------------------------------------------------------------
*/

test(
    'La dernière case possède PG = 51',
    $plateau->getCase(51)->getId() === 51
);


/*
|--------------------------------------------------------------------------
| TEST 4 — Vérification des quatre départs
|--------------------------------------------------------------------------
*/

test(
    'Le départ ROUGE est PG 0',
    $plateau->getIndexDepart(COLOR::ROUGE) === 0
);

test(
    'Le départ BLEU est PG 13',
    $plateau->getIndexDepart(COLOR::BLEU) === 13
);

test(
    'Le départ JAUNE est PG 26',
    $plateau->getIndexDepart(COLOR::JAUNE) === 26
);

test(
    'Le départ VERT est PG 39',
    $plateau->getIndexDepart(COLOR::VERT) === 39
);


/*
|--------------------------------------------------------------------------
| TEST 5 — IRG = 0 au départ de chaque couleur
|--------------------------------------------------------------------------
*/

test(
    'ROUGE : IRG 0 correspond à PG 0',
    $plateau->positionToPG(0, COLOR::ROUGE) === 0
);

test(
    'BLEU : IRG 0 correspond à PG 13',
    $plateau->positionToPG(0, COLOR::BLEU) === 13
);

test(
    'JAUNE : IRG 0 correspond à PG 26',
    $plateau->positionToPG(0, COLOR::JAUNE) === 26
);

test(
    'VERT : IRG 0 correspond à PG 39',
    $plateau->positionToPG(0, COLOR::VERT) === 39
);


/*
|--------------------------------------------------------------------------
| TEST 6 — Avancement IRG → PG
|--------------------------------------------------------------------------
*/

test(
    'ROUGE : IRG 1 → PG 1',
    $plateau->positionToPG(1, COLOR::ROUGE) === 1
);

test(
    'BLEU : IRG 1 → PG 14',
    $plateau->positionToPG(1, COLOR::BLEU) === 14
);

test(
    'JAUNE : IRG 1 → PG 27',
    $plateau->positionToPG(1, COLOR::JAUNE) === 27
);

test(
    'VERT : IRG 1 → PG 40',
    $plateau->positionToPG(1, COLOR::VERT) === 40
);


/*
|--------------------------------------------------------------------------
| TEST 7 — Passage de PG 51 à PG 0
|--------------------------------------------------------------------------
|
| Pour un pion VERT :
|
| IRG 12 → PG 51
| IRG 13 → PG 0
|
*/

test(
    'VERT : IRG 12 → PG 51',
    $plateau->positionToPG(12, COLOR::VERT) === 51
);

test(
    'VERT : IRG 13 → PG 0',
    $plateau->positionToPG(13, COLOR::VERT) === 0
);


/*
|--------------------------------------------------------------------------
| TEST 8 — Fin du circuit
|--------------------------------------------------------------------------
*/

test(
    'ROUGE : IRG 51 → PG 51',
    $plateau->positionToPG(51, COLOR::ROUGE) === 51
);

test(
    'BLEU : IRG 51 → PG 12',
    $plateau->positionToPG(51, COLOR::BLEU) === 12
);

test(
    'JAUNE : IRG 51 → PG 25',
    $plateau->positionToPG(51, COLOR::JAUNE) === 25
);

test(
    'VERT : IRG 51 → PG 38',
    $plateau->positionToPG(51, COLOR::VERT) === 38
);


/*
|--------------------------------------------------------------------------
| TEST 9 — Conversion inverse PG → IRG
|--------------------------------------------------------------------------
*/

test(
    'ROUGE : PG 0 → IRG 0',
    $plateau->PGToPosition(0, COLOR::ROUGE) === 0
);

test(
    'BLEU : PG 13 → IRG 0',
    $plateau->PGToPosition(13, COLOR::BLEU) === 0
);

test(
    'JAUNE : PG 26 → IRG 0',
    $plateau->PGToPosition(26, COLOR::JAUNE) === 0
);

test(
    'VERT : PG 39 → IRG 0',
    $plateau->PGToPosition(39, COLOR::VERT) === 0
);


/*
|--------------------------------------------------------------------------
| TEST 10 — Conversion inverse après déplacement
|--------------------------------------------------------------------------
*/

test(
    'BLEU : PG 14 → IRG 1',
    $plateau->PGToPosition(14, COLOR::BLEU) === 1
);

test(
    'JAUNE : PG 27 → IRG 1',
    $plateau->PGToPosition(27, COLOR::JAUNE) === 1
);

test(
    'VERT : PG 40 → IRG 1',
    $plateau->PGToPosition(40, COLOR::VERT) === 1
);


/*
|--------------------------------------------------------------------------
| TEST 11 — Calcul du prochain IRG
|--------------------------------------------------------------------------
*/

test(
    'IRG 0 + 6 = IRG 6',
    $plateau->calculateNextPosition(0, 6) === 6
);

test(
    'IRG 10 + 5 = IRG 15',
    $plateau->calculateNextPosition(10, 5) === 15
);

test(
    'IRG 50 + 1 revient à IRG 51',
    $plateau->calculateNextPosition(50, 1) === 51
);

test(
    'IRG 51 + 1 revient à IRG 0',
    $plateau->calculateNextPosition(51, 1) === 0
);


/*
|--------------------------------------------------------------------------
| TEST 12 — Vérification complète IRG → PG → IRG
|--------------------------------------------------------------------------
|
| On vérifie automatiquement les 52 positions
| pour chaque couleur.
|--------------------------------------------------------------------------
*/

echo '<h2>TEST DE COHÉRENCE DES 52 POSITIONS</h2>';

foreach (COLOR::cases() as $couleur) {

    $ok = true;

    for ($irg = 0; $irg < 52; $irg++) {

        # Première conversion : IRG → PG.
        $pg = $plateau->positionToPG($irg, $couleur);

        # Deuxième conversion : PG → IRG.
        $irgRetour = $plateau->PGToPosition($pg, $couleur);

        if ($irgRetour !== $irg) {
            $ok = false;

            echo "❌ {$couleur->value} : "
                . "IRG {$irg} → PG {$pg} → IRG {$irgRetour}<br>";
        }
    }

    test(
        "{$couleur->value} : les 52 positions sont cohérentes",
        $ok
    );
}


/*
|--------------------------------------------------------------------------
| AFFICHAGE DE LA TABLE IRG → PG
|--------------------------------------------------------------------------
|
| Cette partie nous permet de comparer directement avec quadrant.txt.
|--------------------------------------------------------------------------
*/

echo '<h2>TABLE IRG → PG</h2>';

foreach (COLOR::cases() as $couleur) {

    echo "<h3>{$couleur->value}</h3>";

    echo '<table border="1" cellpadding="5">';
    echo '<tr>';
    echo '<th>IRG</th>';
    echo '<th>PG</th>';
    echo '</tr>';

    for ($irg = 0; $irg < 52; $irg++) {

        $pg = $plateau->positionToPG($irg, $couleur);

        echo '<tr>';
        echo "<td>{$irg}</td>";
        echo "<td>{$pg}</td>";
        echo '</tr>';
    }

    echo '</table>';
}
?>