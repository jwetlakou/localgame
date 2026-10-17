<?php
declare(strict_types=1);

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/des.php';
require_once __DIR__ . '/src/joueur.php';
require_once __DIR__ . '/src/movemanage.php';

use App\Cases;
use App\COLOR;
use App\Des;
use App\ETAT_PION;
use App\GestionnaireMouvement;
use App\Joueur;
use App\PawnStack;
use App\Pion;
use App\Plateau;
use App\TYPE_CASE;
use RuntimeException;
use Throwable;

final class TestRunner {
    private  int $reussis = 0;
    private  int $echoues = 0;
    private  array $erreurs = [];

    public function test (string $nom, callable $test): void {
        try {
            $test(); $this->reussis++;
            echo "[OK] {$nom} <br/>";
        } catch (Throwable $exception) {
            $this->echoues++;
            $this->erreurs[] = ['nom' => $nom, 'message' => $exception->getMessage()];
            echo "[ECHEC] {$nom} <br/>";
            echo "{$exception->getMessage()} <br/>";
        }
    }

    public function assertTrue (bool $condition, string $message): void {
        if (!$condition) { throw new RuntimeException($message); }
    }

    public function assertSame (mixed $attendu, mixed $obtenu, string $message): void {
        if ($attendu !== $obtenu) { throw new RuntimeException($message . "| ATTENDU : " . var_export($attendu, true) . " | OBTENU : " . var_export($obtenu, true)); }
    }

    public function assertThrows (callable $action, string $message): void {
        try { $action(); }
        catch (Throwable) { return; }
        throw new RuntimeException($message);
    }

    public function finish (): never {
        echo "<br/>";
        echo "===============<br/>";
        echo "tests réussis : {$this->reussis} <br/>";
        echo "tests échoués : {$this->echoues} <br/>";
        echo "===============<br/>";
        exit ($this->echoues === 0 ? 0 : 1);
    }
}

$tests = new TestRunner();

$tests->test('PION : état initial', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    
    $tests->assertSame(ETAT_PION::ETAT_MAISON, $pion->getState(), 'un pion initial doit être en maison');
    $tests->assertSame(-1, $pion->getPosition(), 'position initiale en MAISON');
    $tests->assertSame(null, $pion->getDetenuPar(), 'un pion en MAISON ne doit pas être detenu');
});

$tests->test('PION : identifiant invalide', function () use ($tests): void {
    $tests->assertThrows(fn() => new Pion(0, COLOR::ROUGE), 'un identifiant nul doit être réfusé');
});

$tests->test('PION : sortie au DEPART', function () use ($tests): void {
    $pion = new Pion(1, COLOR::BLEU);
    $pion->startZone();

    $tests->assertSame(ETAT_PION::ETAT_CIRCUIT, $pion->getState(), 'état après sortie');
    $tests->assertSame(0, $pion->getPosition(), 'position après sortie');
    $tests->assertThrows(fn()=> $pion->moveTo(60), 'position invalide');
    $tests->assertThrows(fn()=> $pion->moveTo(-1), 'position invalide');
});

$tests->test('PION : ARRIVEE et CENTRE', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    $pion->startZone();
    $pion->moveTo(52);
    
    $tests->assertSame(ETAT_PION::ETAT_ARRIVEE, $pion->getState(), 'état arrivée');
    $tests->assertThrows(fn()=> $pion->release(), 'pion en ARRIVEE peut pas peut pas être libéré');
    $tests->assertThrows(fn()=> $pion->moveTo(51), 'pion en ARRIVEE peut pas retourner sur le CIRCUIT');

    $pion->moveTo(57);

    $tests->assertSame(ETAT_PION::ETAT_CENTRE, $pion->getState(), 'état centre');
    $tests->assertSame(57, $pion->getPosition(), 'position centre');
    $tests->assertThrows(fn()=> $pion->release(), 'pion en CENTRE peut pas peut pas être libéré');
    $tests->assertThrows(fn()=> $pion->moveTo(54), 'pion en CENTRE peut pas retourner sur ARRIVEE');
    $tests->assertThrows(fn()=> $pion->moveTo(50), 'pion en CENTRE peut pas retourner sur CIRCUIT');
});

$tests->test('PION : mouvement impossible depuis MAISON', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    
    $tests->assertThrows(fn()=> $pion->moveTo(3), 'pion en MAISON peut pas se deplacer normalement');
    $tests->assertThrows(fn()=> $pion->moveTo(52), 'pion en MAISON peut pas se trouver en ARRIVEE');
    $tests->assertThrows(fn()=> $pion->moveTo(57), 'pion en MAISON peut pas se trouver en CENTRE');
});

$tests->test('PION : CAPTURE et LIBÉRATION', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    $tests->assertThrows(fn()=> $pion->takenBy(COLOR::BLEU), 'pion en MAISON peut pas être capturé');
    $tests->assertThrows(fn()=> $pion->release(), 'pion en MAISON peut pas peut pas être libéré');

    $pion->startZone();
    $tests->assertThrows(fn()=> $pion->release(), 'pion en CIRCUIT peut pas peut pas être libéré');
    $tests->assertThrows(fn()=> $pion->takenBy(COLOR::ROUGE), 'pion peut pas être capturé par sa COULEUR');
    
    $pion->takenBy(COLOR::BLEU);
    $tests->assertSame(ETAT_PION::ETAT_OTAGE, $pion->getState(), 'pion doit être en OTAGE');
    $tests->assertSame(COLOR::BLEU, $pion->getDetenuPar(), 'detenteur non null');
    $tests->assertThrows(fn()=> $pion->startZone(), 'IMPOSSIBLE : PION CAPTURE - PION N\'EST PAS A LA MAISON');
    $tests->assertThrows(fn()=> $pion->moveTo(4), 'pion en OTAGE peut pas se trouver sur CIRCUIT');
    $tests->assertThrows(fn()=> $pion->moveTo(52), 'pion en OTAGE peut pas se trouver en ARRIVEE');
    $tests->assertThrows(fn()=> $pion->moveTo(57), 'pion en OTAGE peut pas se trouver en CENTRE');

    $pion->release();
    $tests->assertSame(ETAT_PION::ETAT_MAISON, $pion->getState(), 'pion libéré doit revenir en MAISON');
    $tests->assertSame(null, $pion->getDetenuPar(), 'detenteur doit être effacé');
});
// ===========================================================
// ===========================================================

$tests->test('PILE : ORDRE LIFO', function () use ($tests): void {
    $pile = new PawnStack();
    $pion1 = new Pion(1, COLOR::ROUGE);
    $pion2 = new Pion(2, COLOR::ROUGE);
    
    $pile->push($pion1);
    $pile->push($pion2);

    $tests->assertTrue($pile->contains($pion2), 'pile contient pion #2');
    $tests->assertSame(2, $pile->count(), 'pile contient 2 pions');
    $tests->assertSame($pion2, $pile->peek(), 'dernier pion ajouté doit être au sommet');
    $tests->assertSame($pion2, $pile->pop(), 'dernier pion ajouté doit être sortir en premier');
    $tests->assertSame($pion1, $pile->pop(), 'premier pion ajouté doit sortir ensuite');
    $tests->assertTrue($pile->isEmpty(), 'pile doit être vide');
});

$tests->test('PILE : ACTIONS IMPOSSIBLES', function () use ($tests): void {
    $pile = new PawnStack();
    $tests->assertThrows(fn() => $pile->pop(), 'retirer un pion d\'une pile vide');
    $tests->assertThrows(fn() => $pile->peek(), 'consulter un pion d\'une pile vide');
});
// ===========================================================
// ===========================================================


$tests->test('CASES : DEPART accepte plusieur couleurs', function () use ($tests): void {
    $depart = new Cases(13, TYPE_CASE::DEPART, COLOR::BLEU);
    $tests->assertTrue($depart->isEmpty(), 'case de DEPART est vide');
    $rouge = new Pion(1, COLOR::ROUGE); $bleu = new Pion(1, COLOR::BLEU);
    $jaune = new Pion(1, COLOR::JAUNE); $vert = new Pion(1, COLOR::VERT);

    $depart->addPawn($rouge); $depart->addPawn($bleu);
    $depart->addPawn($jaune); $depart->addPawn($vert);

    $tests->assertSame(4, $depart->countPawns(), 'pile de case DEPART contient 4 pions');
    $tests->assertTrue($depart->isPawnActive($vert), 'dernier pion ajouté doit être actif');
    $tests->assertTrue(!$depart->isPawnActive($rouge), 'pion inférieur doit être inactif');
    $tests->assertTrue($depart->canAcceptPawns(new Pion(2, COLOR::JAUNE)), 'DEPART peut accepter ce pion');
    $tests->assertSame($vert, $depart->getTopPawn(), 'pion au SOMMET de la case');
    $tests->assertSame($vert, $depart->removeTopPawn(), 'pion au SOMMET est retiré');
    $tests->assertTrue(!$depart->containsPawn($vert), 'ce pion n\'est plus sur la case');
});

$tests->test('CASES : NEUTRE accepte une pile de même couleur', function () use ($tests): void {
    $neutre4 = new Cases(4);
    $pion1 = new Pion(1, COLOR::ROUGE); $pion2 = new Pion(2, COLOR::ROUGE);
    $pion3 = new Pion(3, COLOR::ROUGE); $pion4 = new Pion(4, COLOR::ROUGE);

    $neutre4->addPawn($pion1); $neutre4->addPawn($pion2);
    $tests->assertSame(2, $neutre4->countPawns(), '2 pions de même couleur');
    
    $neutre4->addPawn($pion3); $neutre4->addPawn($pion4);
    $tests->assertSame(4, $neutre4->countPawns(), '4 pions de même couleur');
    $tests->assertThrows(fn() => $neutre4->addPawn(new Pion(5, COLOR::ROUGE)), 'case NEUTRE doit pas accepter 5e pion');
    
    $neutre4->removeTopPawn();
    $tests->assertSame(3, $neutre4->countPawns(), '3 pions de même couleur');
    $tests->assertThrows(fn() => $neutre4->addPawn(new Pion(2, COLOR::JAUNE)), 'case NEUTRE doit pas accepter ce pion dans cette PILE');
    $neutre4->clear();
    $tests->assertSame(0, $neutre4->countPawns(), 'retirer tous les pions');
    $tests->assertTrue($neutre4->isEmpty(), 'case NEUTRE vide');
    
    // $tests->assertThrows(fn () => $neutre4->removeTopPawn(), 'IMPOSSIBLE DE RETIRER SUR UNE CASE VIDE');
    $tests->assertTrue($neutre4->canAcceptPawns(new Pion(2, COLOR::JAUNE)), 'case NEUTRE vide accepte ce pion');
});

$tests->test('CASES : test sur ARRIVEE', function () use ($tests): void {
    $arrivee52 = new Cases(52, TYPE_CASE::ARRIVEE, COLOR::ROUGE);
    $pion1 = new Pion(1, COLOR::ROUGE); $pion2 = new Pion(2, COLOR::ROUGE);
    $pion3 = new Pion(3, COLOR::ROUGE); $pion4 = new Pion(4, COLOR::ROUGE);

    $arrivee52->addPawn($pion1); $arrivee52->addPawn($pion2);
    $tests->assertSame(2, $arrivee52->countPawns(), '2 pions de même couleur');
    $tests->assertThrows(fn() => $arrivee52->addPawn(new Pion(1, COLOR::BLEU)), 'case ARRIVEE doit pas accepter pion BLEU');

    $arrivee52->addPawn($pion3); $arrivee52->addPawn($pion4);
    $tests->assertSame(4, $arrivee52->countPawns(), '4 pions de même couleur');
});
// ===========================================================
// ===========================================================


$tests->test('JOUEUR : possède exactement 4 pions', function () use ($tests): void {
    $joueur = new Joueur(17, 'JOHN', COLOR::BLEU);

    $tests->assertSame(4, count($joueur->getPionsByEtat(ETAT_PION::ETAT_MAISON)), 'joueur doit posséder 4 pions en MAISON');
    $tests->assertSame(1, $joueur->getPionById(1)?->getId(), 'recherche de pion incorrecte');
});

$tests->test('JOUEUR : rejette un pion appartenant à un joueur', function () use ($tests): void {
    $joueur = new Joueur(1, 'JOHN', COLOR::BLEU);
    $pionAdverse = new Pion(1, COLOR::ROUGE);

    $tests->assertTrue(!$joueur->hasPawn($pionAdverse), 'joueur peut pas posséder pion adverse');
});

$tests->test('JOUEUR : victoire seulement avec 4 pions', function () use ($tests): void {
    $joueur = new Joueur(1, 'BILLY', COLOR::ROUGE);

    $tests->assertTrue(!$joueur->isWin(), 'joueur en debut de partie doit pas avoir gagné');
    foreach ($joueur->getPionsByEtat(ETAT_PION::ETAT_MAISON) as $pion) {
        $pion->startZone();$pion->moveTo(57);
    }
    $tests->assertTrue($joueur->isWin(), '4 pions au centre doivent declencher la victoire');
});
// ===========================================================
// ===========================================================

$tests->test('DES : valeurs générées comprise entre 1 & 6', function () use ($tests): void {
    $des = new Des(); $resultat = $des->roll();
    
    $tests->assertTrue($resultat['de1'] >= 1 && $resultat['de1'] <=6, 'resultat dés compris entre 1 & 6');
    $tests->assertTrue($resultat['de2'] >= 1 && $resultat['de2'] <=6, 'resultat dés compris entre 1 & 6');
});

$tests->test('DES : somme et minimum/maximum cohérents', function () use ($tests): void {
    $des = new Des(); $resultat = $des->roll();

    $tests->assertSame($resultat['de1'] + $resultat['de2'], $resultat['somme'], 'somme des dés incorrecte');
    $tests->assertSame(max($resultat['de1'], $resultat['de2']), $resultat['deMax'], 'dé maximum incorrecte');
    $tests->assertSame(min($resultat['de1'], $resultat['de2']), $resultat['deMin'], 'dé minimum incorrecte');
});
// ===========================================================
// ===========================================================

$tests->test('PLATEAU : 76 cases disponible', function () use ($tests): void {
    $plateau = new Plateau();
    for ($pg=0; $pg < 76; $pg++) { $tests->assertSame($pg, $plateau->getCase($pg)->getId(), "case PG {$pg} incorrecte"); }
});

$tests->test('PLATEAU : positions de depart par couleur', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertSame(0, $plateau->getIndexDepart(COLOR::ROUGE), 'depart rouge incorrect');
    $tests->assertSame(13, $plateau->getIndexDepart(COLOR::BLEU), 'depart bleu incorrect');
    $tests->assertSame(26, $plateau->getIndexDepart(COLOR::JAUNE), 'depart jaune incorrect');
    $tests->assertSame(39, $plateau->getIndexDepart(COLOR::VERT), 'depart vert incorrect');
});

$tests->test('PLATEAU : conversion IRG vers PG', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertSame(0, $plateau->positionToPG(0, COLOR::ROUGE), 'IRG 0 rouge incorrect');
    $tests->assertSame(13, $plateau->positionToPG(0, COLOR::BLEU), 'IRG 0 bleu incorrect');
    $tests->assertSame(26, $plateau->positionToPG(0, COLOR::JAUNE), 'IRG 0 jaune incorrect');
    $tests->assertSame(39, $plateau->positionToPG(0, COLOR::VERT), 'IRG 0 vert incorrect');
});

$tests->test('PLATEAU : mouvement au-delà du centre refusé', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertThrows(fn () => $plateau->calculateNextPosition(56, 2), 'deplacement doit pas depasser le centre');
});

// $tests->test('GESTIONNAIRE : un pion actif qui se déplace dans sa voie d\'arrivée', function () use ($tests): void {
//     $gestionnaire = new GestionnaireMouvement();
//     $plateau = new Plateau();
//     $john = new Joueur(2, 'JOHN', COLOR::ROUGE);

//     $testJohn = $john->getPionById(1);
//     if ($testJohn === null) { throw new RuntimeException("PION EST INTROUVABLE"); }

//     $testJohn->startZone(); $testJohn->moveTo(52);
//     $caseArrivee = $plateau->getCase($plateau->positionToPG($testJohn->getPosition(), $testJohn->getColor()));
//     $caseArrivee->addPawn($testJohn);
//     $caseArrivee->removeTopPawn();

//     $testJohn->moveTo(56);
//     $caseArrivee = $plateau->getCase($plateau->positionToPG($testJohn->getPosition(), $testJohn->getColor()));
//     $caseArrivee->addPawn($testJohn);

//     echo "<pre>";
//     var_dump([
//         'position' => $testJohn->getPosition(),
//         'etat' => $testJohn->getState()->value,
//         'PG' => $plateau->getCase($plateau->positionToPG($testJohn->getPosition(), $testJohn->getColor())),
//     ]);
//     echo "</pre>";
// });

$tests->test('GESTIONNAIRE : un deplacement qui depasse la dernière case autorisée', function () use ($tests): void {
    $gestionnaire = new GestionnaireMouvement();
    $plateau = new Plateau();
    $john = new Joueur(2, 'JOHN', COLOR::ROUGE);

    $pionJohn = $john->getPionById(1);
    if ($pionJohn === null) { throw new RuntimeException("PION EST INTROUVABLE"); }

    $pionJohn->startZone(); $pionJohn->moveTo(52);
    $pg = $plateau->positionToPG($pionJohn->getPosition(), $pionJohn->getColor());
    $caseArrivee = $plateau->getCase($pg);

    if ($pionJohn->getState() !== ETAT_PION::ETAT_ARRIVEE) { throw new RuntimeException("PION DEVRAIT ETRE DANS LA VOIE D'ARRIVEE"); }
    
    echo "<pre>";
    var_dump([
        'position_relative' => $pionJohn->getPosition(),
        'etat_pion' => $pionJohn->getState()->value,
        'couleur_pion' => $pionJohn->getColor()->value,
        'PG_CASE' => $caseArrivee->getId(),
        'type_CASE' => $caseArrivee->getType()->value,
        'secteur_CASE' => $caseArrivee->getColorSector()->value,
        'nombre_pion' => $caseArrivee->countPawns(),
        'pion_deja_present' => $caseArrivee->containsPawn($pionJohn),
        'peut_accepter' => $caseArrivee->canAcceptPawns($pionJohn),
    ]);
    echo "</pre>";

    // $caseArrivee->addPawn($pionJohn);
    // $caseArrivee->addPawn($pionJohn);

    // echo "<pre>";
    // var_dump([
    //     'position_relative' => $pionJohn->getPosition(),
    //     'etat_pion' => $pionJohn->getState()->value,
    //     'couleur_pion' => $pionJohn->getColor()->value,
    //     'PG_CASE' => $caseArrivee->getId(),
    //     'type_CASE' => $caseArrivee->getType()->value,
    //     'secteur_CASE' => $caseArrivee->getColorSector()->value,
    //     'nombre_pion' => $caseArrivee->countPawns(),
    //     'pion_deja_present' => $caseArrivee->containsPawn($pionJohn),
    //     'peut_accepter' => $caseArrivee->canAcceptPawns($pionJohn),
    // ]);
    // echo "</pre>";
    // $caseArrivee->removeTopPawn();

    // echo "<pre>";
    // var_dump([
    //     'position_relative' => $pionJohn->getPosition(),
    //     'etat_pion' => $pionJohn->getState()->value,
    //     'couleur_pion' => $pionJohn->getColor()->value,
    //     'PG_CASE' => $caseArrivee->getId(),
    //     'type_CASE' => $caseArrivee->getType()->value,
    //     'secteur_CASE' => $caseArrivee->getColorSector()->value,
    //     'nombre_pion' => $caseArrivee->countPawns(),
    //     'pion_deja_present' => $caseArrivee->containsPawn($pionJohn),
    //     'peut_accepter' => $caseArrivee->canAcceptPawns($pionJohn),
    // ]);
    // echo "</pre>";

    $evaluation1 = $gestionnaire->evaluateMove($pionJohn, 4, $plateau);
    $tests->assertTrue($evaluation1['valide'], 'EVALUATION POSSIBLE');
    $tests->assertSame(56, $evaluation1['details']['nouvellePos'], 'NOUVELLE POSITION');
    $caseArrivee = $plateau->getCase($plateau->positionToPG($pionJohn->getPosition(), $pionJohn->getColor()));
    


    $evaluation2 = $gestionnaire->evaluateMove($pionJohn, 2, $plateau);
    $tests->assertTrue($evaluation2['valide'], 'EVALUATION DEPASSEMENT');

    echo "<pre>";
    var_dump([
        'position_relative' => $pionJohn->getPosition(),
        'etat_pion' => $pionJohn->getState()->value,
        'couleur_pion' => $pionJohn->getColor()->value,
        'PG_CASE' => $caseArrivee->getId(),
        'type_CASE' => $caseArrivee->getType()->value,
        'secteur_CASE' => $caseArrivee->getColorSector()->value,
        'nombre_pion' => $caseArrivee->countPawns(),
        'pion_deja_present' => $caseArrivee->containsPawn($pionJohn),
        'peut_accepter' => $caseArrivee->canAcceptPawns($pionJohn),
    ]);
    echo "</pre>";
});

echo "<br/>";
echo "NOTE : les règles métier du Gestionnaire (sorties, IG, priorité des dés) ne sont pas encore exécutées par ce fichier. <br/>Elles doivent être ajoutées avec une API déterministe du Gestionnaire.";
echo "<br/>";

$tests->finish();
?>
