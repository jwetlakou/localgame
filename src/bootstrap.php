<?php
declare(strict_types=1);

// require_once __DIR__ . '/src/pions.php';
// require_once __DIR__ . '/src/pawnstack.php';
// require_once __DIR__ . '/src/cases.php';
// require_once __DIR__ . '/src/plateau.php';
// require_once __DIR__ . '/src/des.php';
// require_once __DIR__ . '/src/joueur.php';

// use App\COLOR;
// use App\ETAT_PION;
// use App\TYPE_CASE;
// use App\Pion;
// use App\PawnStack;
// use App\Cases;
// use App\Plateau;
// use App\Des;
// use App\Joueur;

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

set_exception_handler( function (\Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'succes' => false,
        'message' => "ERREUR INTERNE SUR LE SERVEUR"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
});
?>