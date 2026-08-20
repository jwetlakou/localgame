<?php
// use App\Cases;
use App\Des;
use App\Joueur;
use App\Pion;
use App\Plateau;
use App\GestionnaireMouvement;
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/des.php';
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/movemanage.php';

// 1- intialiser le plateau
$plateau = new Plateau();

// 2- initialiser les mouvement
$mouvement = new GestionnaireMouvement();

// 1- lancer des dés
$des = new Des();
if (in_array(6, $des->lancer())) {
    $de = 6;
} else {
    $de = $des->lancer()[0];
}
global $de;

// 2- création des 2 joueurs
$casimir = new Joueur(1, 'Casimir', 'ROUGE', false);
// $placide = new Joueur(2, 'PLACIDE', 'BLEU', true);

// 3- création de la case depart ROUGE pour casimir
// $caseDepartRouge = new Cases(0, Cases::TYPE_DEPART, 'ROUGE');

// 3- création de la case depart BLEU pour casimir
// $caseDepartBleu = new Cases(0, Cases::TYPE_DEPART, 'BLEU');

// 4- action 1: casimir sort son pion 1 sur case
$pionCasimir1 = $casimir->getPionById(1);
$pionCasimir1->deplacerVers(10, Pion::ETAT_CIRCUIT);
// $caseDepartRouge->empiler($pionCasimir1);

// 5- determinons l'orde priorité des dés
$ordreDes = $mouvement->obtenirOrdrePrioriteDes($des);
$premierDe = $ordreDes[0];
$secondDe = $ordreDes[1];

// 6 - exécution du 1e déplacement avec le dé supérieur
$mouvement1 = $mouvement->executerMouvement($pionCasimir1, $premierDe, $plateau);

// 7 - exécution du 1e déplacement avec le dé inférieur
$mouvement2 = $mouvement->executerMouvement($pionCasimir1, $secondDe, $plateau);

echo json_encode([
    'statut'            => 'SUCCES',
    'message'           => 'test de synthese phase 1 réussi !',
    'tests'             => [
        'lancer'        => $des->toArray(),
    ],
    'ordreJouer'        => [
        'dePrioritaire' => $premierDe,
        'deSecondaire'  => $secondDe,
    ],
    'etapeMouvement'    => [
        'etapeDeMax'    => $mouvement1,
        'etapeDeMin'    => $mouvement2,
    ],
    'positionfinalPion' => $pionCasimir1->toArray()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>