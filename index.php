<?php

declare(strict_types=1);

require_once __DIR__ . '/src/des.php';

use App\Des;

$des = new Des();

echo "<h1>Test de la classe Des</h1>";


// ============================================================
// TEST 1 — État initial
// ============================================================

echo "<h2>1. État initial</h2>";

$values = $des->getValues();

var_dump($values);

if ($values === [null, null]) {
    echo "✅ Les dés sont correctement à null avant le lancer.<br>";
} else {
    echo "❌ Échec : les dés ne sont pas à null.<br>";
}

if ($des->isDouble() === false) {
    echo "✅ isDouble() retourne false avant le lancer.<br>";
} else {
    echo "❌ Échec : isDouble() devrait retourner false.<br>";
}

if ($des->containsSix() === false) {
    echo "✅ containsSix() retourne false avant le lancer.<br>";
} else {
    echo "❌ Échec : containsSix() devrait retourner false.<br>";
}

if ($des->getDiceMax() === null) {
    echo "✅ getDiceMax() retourne null avant le lancer.<br>";
} else {
    echo "❌ Échec : getDiceMax() devrait retourner null.<br>";
}

if ($des->getDiceMin() === null) {
    echo "✅ getDiceMin() retourne null avant le lancer.<br>";
} else {
    echo "❌ Échec : getDiceMin() devrait retourner null.<br>";
}

if ($des->getDiceSum() === null) {
    echo "✅ getDiceSum() retourne null avant le lancer.<br>";
} else {
    echo "❌ Échec : getDiceSum() devrait retourner null.<br>";
}


// ============================================================
// TEST 2 — Lancer les dés
// ============================================================

echo "<h2>2. Premier lancer</h2>";

$resultat = $des->roll();

var_dump($resultat);


// Vérification des deux valeurs
$values = $des->getValues();

if (
    count($values) === 2 &&
    is_int($values[0]) &&
    is_int($values[1]) &&
    $values[0] >= 1 &&
    $values[0] <= 6 &&
    $values[1] >= 1 &&
    $values[1] <= 6
) {
    echo "✅ Les deux dés contiennent des valeurs entre 1 et 6.<br>";
} else {
    echo "❌ Échec : valeur de dé incorrecte.<br>";
}


// ============================================================
// TEST 3 — Vérification de la somme
// ============================================================

echo "<h2>3. Somme</h2>";

$de1 = $values[0];
$de2 = $values[1];

$sommeAttendue = $de1 + $de2;

if ($des->getDiceSum() === $sommeAttendue) {
    echo "✅ La somme est correcte : {$sommeAttendue}.<br>";
} else {
    echo "❌ Échec de la somme.<br>";
}


// ============================================================
// TEST 4 — Minimum et maximum
// ============================================================

echo "<h2>4. Minimum / Maximum</h2>";

$minAttendu = min($de1, $de2);
$maxAttendu = max($de1, $de2);

if ($des->getDiceMin() === $minAttendu) {
    echo "✅ Minimum correct : {$minAttendu}.<br>";
} else {
    echo "❌ Minimum incorrect.<br>";
}

if ($des->getDiceMax() === $maxAttendu) {
    echo "✅ Maximum correct : {$maxAttendu}.<br>";
} else {
    echo "❌ Maximum incorrect.<br>";
}


// ============================================================
// TEST 5 — Double
// ============================================================

echo "<h2>5. Vérification du double</h2>";

$doubleAttendu = ($de1 === $de2);

if ($des->isDouble() === $doubleAttendu) {
    echo "✅ isDouble() est correct.<br>";
} else {
    echo "❌ isDouble() est incorrect.<br>";
}


// ============================================================
// TEST 6 — Présence d'un six
// ============================================================

echo "<h2>6. Vérification du six</h2>";

$sixAttendu = ($de1 === 6 || $de2 === 6);

if ($des->containsSix() === $sixAttendu) {
    echo "✅ containsSix() est correct.<br>";
} else {
    echo "❌ containsSix() est incorrect.<br>";
}


// ============================================================
// TEST 7 — Cohérence de toArray()
// ============================================================

echo "<h2>7. toArray()</h2>";

$array = $des->toArray();

var_dump($array);

if (
    isset($array['des']) &&
    $array['des'] === [$de1, $de2] &&
    $array['de1'] === $de1 &&
    $array['de2'] === $de2 &&
    $array['somme'] === $de1 + $de2 &&
    $array['estDouble'] === ($de1 === $de2) &&
    $array['avoirSix'] === ($de1 === 6 || $de2 === 6) &&
    $array['deMax'] === max($de1, $de2) &&
    $array['deMin'] === min($de1, $de2)
) {
    echo "✅ toArray() contient des données cohérentes.<br>";
} else {
    echo "❌ Échec de toArray().<br>";
}


// ============================================================
// TEST 8 — Plusieurs lancers
// ============================================================

echo "<h2>8. Plusieurs lancers</h2>";

$nombreDeLancers = 100;

$testReussi = true;

for ($i = 1; $i <= $nombreDeLancers; $i++) {

    $des->roll();

    [$de1, $de2] = $des->getValues();

    if (
        !is_int($de1) ||
        !is_int($de2) ||
        $de1 < 1 ||
        $de1 > 6 ||
        $de2 < 1 ||
        $de2 > 6
    ) {
        $testReussi = false;
        break;
    }
}

if ($testReussi) {
    echo "✅ {$nombreDeLancers} lancers valides.<br>";
} else {
    echo "❌ Une valeur invalide a été détectée.<br>";
}


// ============================================================
// RÉSUMÉ
// ============================================================

echo "<hr>";
echo "<h2>🎲 Test terminé</h2>";

echo "Si aucun ❌ n'apparaît, la classe Des fonctionne correctement.<br>";
?>