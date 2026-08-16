<?php
// use App\Cases;
// use App\Des;
// use App\Joueur;
// use App\Pion;
use App\Plateau;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/des.php';
require_once __DIR__ . '/src/plateau.php';

// // 1- lancer des dés
// $des = new Des();

// // 2- création des 2 joueurs
// $casimir = new Joueur(1, 'Casimir', 'ROUGE', false);
// $placide = new Joueur(2, 'PLACIDE', 'BLEU', true);

// // 3- création de la case depart ROUGE pour casimir
// $caseDepartRouge = new Cases(0, Cases::TYPE_DEPART, 'ROUGE');

// // 3- création de la case depart BLEU pour casimir
// $caseDepartBleu = new Cases(0, Cases::TYPE_DEPART, 'BLEU');

// // 4- action 1: casimir sort son pion 1 sur case
// $pionCasimir1 = $casimir->getPionById(1);
// $pionCasimir1->deplacerVers(0, Pion::ETAT_CIRCUIT);
// $caseDepartRouge->empiler($pionCasimir1);

// // 5- action 2: placide sort son pion 1 sur case
// $pionPlacide1 = $placide->getPionById(2);
// $pionPlacide1->deplacerVers(0, Pion::ETAT_CIRCUIT);
// $caseDepartBleu->empiler($pionPlacide1);

// // 6- action 3: casimir capture le pion 1 de placide
// $pionPlacide2 = $placide->getPionById(1);
// $pionPlacide2->capturer('ROUGE');

// // 7- action 4: placide sort son pion 1 sur case
// $pionPlacide3 = $placide->getPionById(1);
// $pionPlacide3->deplacerVers(0, Pion::ETAT_CIRCUIT);
// $caseDepartBleu->empiler($pionPlacide1);

$plateau = new Plateau();
// test 1- poin à la case 50 avance de 4
$posInitial1 = 50;
$de1 = 4;
$nouvellePos1 = $plateau->calculerNouvellePosition($posInitial1, $de1);

// test 2- distance entre 50 et depart bleu
$distanceVersBleu = $plateau->calculerDistance(50, 13);

// test 3- calcul automatique du prochain depart pour pion situé 10
$posPion = 10;
$prochainDepartInfo = $plateau->distanceProchainDepart($posPion);

// 8- affichage du resultat
echo json_encode([
    'statut'            => 'SUCCES',
    'message'           => 'test de synthese phase 1 réussi !',
    'tests'             => [
        'test_passage_boucle'   => [
            'positionInitiale'  => $posInitial1,
            'valeur'            => $de1,
            'nouvellePosition'  => $nouvellePos1
        ],
        'test_distance_directe'   => [
            'deCase'            => 50,
            'versCase'          => 13,
            'nouvellePosition'  => $distanceVersBleu
        ],
        'test_prochain_depart'   => [
            'positionActuelle'  => $posPion,
            'prochaineDepart'   => $prochainDepartInfo
        ],
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>