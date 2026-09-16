<?php
// require_once __DIR__ . '/src/bootstrap.php';

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/des.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/movemanage.php';
require_once __DIR__ . '/src/partie.php';

header('Content-type: application/json; charset=utf-8');

use App\Des;
use App\Partie;
use App\Joueur;
use App\Pion;

// echo "test de sécurité 6 : masquage des erreurs système<br/>";
$redPion1 = new Pion(1, 'ROUGE');
$redPion1->startZone();
$redPion1->moveTo(0);
// $redPion1->takenBy('BLEU');





// echo "<br/><br/><br/>FIN DU TEST <br/>";
echo json_encode([
    'statut'            => 'SUCCES',
    'pion1'              => $redPion1->toArray(),
    'message'           => 'test de synthese phase 1 réussi !',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>