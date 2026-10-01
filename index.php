<?php
declare(strict_types=1);

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/pawnstack.php';

// use App\Cases;
use App\COLOR;
// use App\ETAT_PION;
// use App\Pion;
use App\Plateau;
// use App\TYPE_CASE;

function assertSameValue (mixed $excepted, mixed $actual, string $message) {
    if ($excepted !== $actual) { throw new RuntimeException("{$message} | attendu : ". var_export($excepted, true) ." | obtenu : ". var_export($actual, true)); }
    echo "✅ {$message}<br/>";
}

function assertThrows(callable $callback, string $message): void {
    try { $callback(); }
    catch (Throwable) { echo "✅ {$message}<br/>"; return; }
    throw new RuntimeException("❌ {$message} | aucune exception n'a été levée");
}

$plateau = new Plateau();

echo "===== TESTS GÉOMÉTRIE PLATEAU =====<br/><br/>";
echo PHP_EOL . "D — CONVERSION IRG ↔ PG" . PHP_EOL;

$plateau->calculateNextPosition(-1, 5);
?>