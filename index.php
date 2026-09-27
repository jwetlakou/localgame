<?php
declare(strict_types=1);

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/pawnstack.php';

use App\Cases;
use App\COLOR;
use App\ETAT_PION;
use App\Pion;
use App\TYPE_CASE;

function ok (string $message): void { echo "✅ $message<br/>"; }
function fail (string $message): void { echo "❌ $message<br/>"; }
function assertTrue (bool $condition, string $message): void {
    if ($condition) { ok($message); return; }
    fail($message);
}
function assertSame (mixed $expected, mixed $actual, string $message) {
    if ($expected === $actual) { ok($message); return; }
    fail($message. "| attendu = ". var_export($expected, true). " obtenu = ". var_export($actual, true));
}
function assertThrows (string $exceptionClass, callable $action, string $message): void {
    try {
        $action();
        fail($message ." | aucune exception");
    } catch (Throwable $e) {
        if ($e instanceof $exceptionClass) { ok($message); return; }
        fail($message ." | mauvaise exception : ".$e::class);
    }
}

echo "<h2>MEME COULEUR</h2>";

$neutre = new Cases(1, TYPE_CASE::NEUTRE);
$redDepart = new Cases(0, TYPE_CASE::DEPART, COLOR::ROUGE);
$bleuArrivee = new Cases(52, TYPE_CASE::ARRIVEE, COLOR::BLEU);
$greenCentre = new Cases(57, TYPE_CASE::CENTRE, COLOR::VERT);

$pion = new Pion(1, COLOR::BLEU);
$neutre->addPawn($pion);

assertThrows(
    LogicException::class,
    fn() => $neutre->addPawn($pion),
    "un même objet Pion ne peut pas être ajouté deux fois"
);
?>