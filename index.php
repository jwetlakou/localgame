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
    $jaune2 = new Pion(2, COLOR::JAUNE);
    $tests->assertSame(3, $neutre4->countPawns(), '3 pions de même couleur');
    $tests->assertThrows(fn() => $neutre4->addPawn($jaune2), 'case NEUTRE doit pas accepter ce pion dans cette PILE');
    $neutre4->clear();
    $tests->assertSame(0, $neutre4->countPawns(), 'retirer tous les pions');
    $tests->assertTrue($neutre4->isEmpty(), 'case NEUTRE vide');
    
    $tests->assertTrue($neutre4->canAcceptPawns($jaune2), 'case NEUTRE vide accepte ce pion');

    $neutre4->addPawn($jaune2);
    $tests->assertThrows(fn() => $neutre4->addPawn($pion2), 'case NEUTRE doit pas accepter ce pion dans cette PILE');
});

$tests->test('CASES : test sur ARRIVEE et CENTRE', function () use ($tests): void {
    $arrivee52 = new Cases(52, TYPE_CASE::ARRIVEE, COLOR::ROUGE);
    $tests->assertThrows(fn() => $arrivee52->addPawn(new Pion(2, COLOR::BLEU)), 'case ARRIVEE doit pas accepter PION autre COULEUR');
    
    $pion1 = new Pion(1, COLOR::ROUGE); $pion2 = new Pion(2, COLOR::ROUGE);
    $pion3 = new Pion(3, COLOR::ROUGE); $pion4 = new Pion(4, COLOR::ROUGE);

    $arrivee52->addPawn($pion1); $arrivee52->addPawn($pion2);
    $tests->assertSame(2, $arrivee52->countPawns(), '2 pions de même couleur');
    $tests->assertThrows(fn() => $arrivee52->addPawn(new Pion(1, COLOR::BLEU)), 'case ARRIVEE doit pas accepter PION autre COULEUR');

    $arrivee52->addPawn($pion3); $arrivee52->addPawn($pion4);
    $tests->assertSame(4, $arrivee52->countPawns(), '4 pions de même couleur');
    
    $pion5 = new Pion(5, COLOR::ROUGE);
    // $arrivee52->addPawn($pion5);
    // $tests->assertSame(5, $arrivee52->countPawns(), '5 pions de même couleur');
    
    // CETTE PARTIE A IMPLEMENTER [VOIES ARRIVEE DOIT CONTENIR 4 PIONS DE MEME COULEUR AU MAXIMUM] [ELLE DONNE ERREUR POUR L'INSTANT]
    $tests->assertThrows(fn() => $arrivee52->addPawn($pion5), 'case ARRIVEE doit pas accepter 5e pion');

    $centre73 = new Cases(73, TYPE_CASE::CENTRE, COLOR::BLEU);
    $tests->assertThrows(fn() => $centre73->addPawn(new Pion(1, COLOR::VERT)), 'case CENTRE doit pas accepter PION autre COULEUR');

    $bleu1 = new Pion(1, COLOR::BLEU); $bleu2 = new Pion(2, COLOR::BLEU);
    $bleu3 = new Pion(3, COLOR::BLEU); $bleu4 = new Pion(4, COLOR::BLEU);
    
    $centre73->addPawn($bleu1); $centre73->addPawn($bleu2);
    $tests->assertSame(2, $centre73->countPawns(), '2 pions de même couleur');
    $tests->assertThrows(fn() => $centre73->addPawn(new Pion(1, COLOR::JAUNE)), 'case ARRIVEE doit pas accepter PION autre COULEUR');
    
    $centre73->addPawn($bleu3); $centre73->addPawn($bleu4);
    $tests->assertSame(4, $centre73->countPawns(), '2 pions de même couleur');

    $bleu5 = new Pion(5, COLOR::BLEU);
    // $centre73->addPawn($bleu5);
    // $tests->assertSame(5, $centre73->countPawns(), '5 pions de même couleur');

    // CETTE PARTIE A IMPLEMENTER [VOIES CENTRE DOIT CONTENIR 4 PIONS DE MEME COULEUR AU MAXIMUM] [ELLE DONNE ERREUR POUR L'INSTANT]
    $tests->assertThrows(fn() => $centre73->addPawn($bleu5), 'case CENTRE doit pas accepter 5e pion');
});
// ===========================================================
// ===========================================================


$tests->test('JOUEUR : possède exactement 4 pions', function () use ($tests): void {
    $joueur = new Joueur(17, 'JOHN', COLOR::BLEU);
    $tests->assertSame(4, count($joueur->getPionsByEtat(ETAT_PION::ETAT_MAISON)), 'joueur doit posséder 4 pions en MAISON');

    $pionBleu = new Pion(4, COLOR::BLEU); $bleu = $joueur->getPionById(1);
    $tests->assertTrue(!$joueur->hasPawn($pionBleu), 'ce pion n\'appartient pas à ce JOUEUR');
    $tests->assertTrue($joueur->hasPawn($bleu), 'ce pion appartient à ce JOUEUR');

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

    
    $tests->assertTrue($resultat['de1'] >= 1 && $resultat['de1'] <= 6, 'resultat dés compris entre 1 & 6');
    $tests->assertTrue($resultat['de2'] >= 1 && $resultat['de2'] <= 6, 'resultat dés compris entre 1 & 6');
});

$tests->test('DES : somme et minimum/maximum cohérents', function () use ($tests): void {
    $des = new Des(); $resultat = $des->roll();

    $tests->assertSame($resultat['de1'] + $resultat['de2'], $resultat['somme'], 'somme des dés correcte');
    $tests->assertSame(max($resultat['de1'], $resultat['de2']), $resultat['deMax'], 'dé maximum correcte');
    $tests->assertSame(min($resultat['de1'], $resultat['de2']), $resultat['deMin'], 'dé minimum correcte');
});
// ===========================================================
// ===========================================================


$tests->test('PLATEAU : 76 cases disponibles', function () use ($tests): void {
    $plateau = new Plateau();
    for ($pg=0; $pg < 76; $pg++) { $tests->assertSame($pg, $plateau->getCase($pg)->getId(), "case PG {$pg} correcte"); }
});

$tests->test('PLATEAU : position de DEPART par COULEUR', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertSame(0, $plateau->getIndexDepart(COLOR::ROUGE), 'DEPART ROUGE correct');
    $tests->assertSame(13, $plateau->getIndexDepart(COLOR::BLEU), 'DEPART BLEU correct');
    $tests->assertSame(26, $plateau->getIndexDepart(COLOR::JAUNE), 'DEPART JAUNE correct');
    $tests->assertSame(39, $plateau->getIndexDepart(COLOR::VERT), 'DEPART VERT correct');
});

$tests->test('PLATEAU : conversion IRG vers PG', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertSame(0, $plateau->positionToPG(0, COLOR::ROUGE), 'IRG 0 ROUGE correct');
    $tests->assertSame(12, $plateau->positionToPG(12, COLOR::ROUGE), 'IRG 12 ROUGE correct');
    $tests->assertSame(51, $plateau->positionToPG(51, COLOR::ROUGE), 'IRG 51 ROUGE correct');
    $tests->assertSame(52, $plateau->positionToPG(52, COLOR::ROUGE), 'IRG 52 ROUGE correct');
    $tests->assertSame(56, $plateau->positionToPG(56, COLOR::ROUGE), 'IRG 56 ROUGE correct');
    $tests->assertSame(72, $plateau->positionToPG(57, COLOR::ROUGE), 'IRG 57 ROUGE correct');

    $tests->assertSame(13, $plateau->positionToPG(0, COLOR::BLEU), 'IRG 0 BLEU correct');
    $tests->assertSame(25, $plateau->positionToPG(12, COLOR::BLEU), 'IRG 12 BLEU correct');
    $tests->assertSame(12, $plateau->positionToPG(51, COLOR::BLEU), 'IRG 51 BLEU correct');
    $tests->assertSame(57, $plateau->positionToPG(52, COLOR::BLEU), 'IRG 52 BLEU correct');
    $tests->assertSame(61, $plateau->positionToPG(56, COLOR::BLEU), 'IRG 56 BLEU correct');
    $tests->assertSame(73, $plateau->positionToPG(57, COLOR::BLEU), 'IRG 57 BLEU correct');

    $tests->assertSame(26, $plateau->positionToPG(0, COLOR::JAUNE), 'IRG 0 JAUNE correct');
    $tests->assertSame(38, $plateau->positionToPG(12, COLOR::JAUNE), 'IRG 12 JAUNE correct');
    $tests->assertSame(25, $plateau->positionToPG(51, COLOR::JAUNE), 'IRG 51 JAUNE correct');
    $tests->assertSame(62, $plateau->positionToPG(52, COLOR::JAUNE), 'IRG 52 JAUNE correct');
    $tests->assertSame(66, $plateau->positionToPG(56, COLOR::JAUNE), 'IRG 56 JAUNE correct');
    $tests->assertSame(74, $plateau->positionToPG(57, COLOR::JAUNE), 'IRG 57 JAUNE correct');

    $tests->assertSame(39, $plateau->positionToPG(0, COLOR::VERT), 'IRG 0 VERT correct');
    $tests->assertSame(51, $plateau->positionToPG(12, COLOR::VERT), 'IRG 12 VERT correct');
    $tests->assertSame(38, $plateau->positionToPG(51, COLOR::VERT), 'IRG 51 VERT correct');
    $tests->assertSame(67, $plateau->positionToPG(52, COLOR::VERT), 'IRG 52 VERT correct');
    $tests->assertSame(71, $plateau->positionToPG(56, COLOR::VERT), 'IRG 56 VERT correct');
    $tests->assertSame(75, $plateau->positionToPG(57, COLOR::VERT), 'IRG 57 VERT correct');
});

$tests->test('PLATEAU : conversion PG vers IRG', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertSame(0, $plateau->PGToPosition(0, COLOR::ROUGE), 'PG 0 pour ROUGE correct');
    $tests->assertSame(51, $plateau->PGToPosition(51, COLOR::ROUGE), 'PG 51 pour ROUGE correct');
    $tests->assertSame(39, $plateau->PGToPosition(0, COLOR::BLEU), 'PG 0 pour BLEU correct');
    $tests->assertSame(38, $plateau->PGToPosition(51, COLOR::BLEU), 'PG 51 pour BLEU correct');
    $tests->assertSame(26, $plateau->PGToPosition(0, COLOR::JAUNE), 'PG 0 pour JAUNE correct');
    $tests->assertSame(25, $plateau->PGToPosition(51, COLOR::JAUNE), 'PG 51  pour JAUNE correct');
    $tests->assertSame(13, $plateau->PGToPosition(0, COLOR::VERT), 'PG 0 pour VERT correct');
    $tests->assertSame(12, $plateau->PGToPosition(51, COLOR::VERT), 'PG 51 pour VERT correct');
});

$tests->test('PLATEAU : mouvement au-delà du centre refusé', function () use ($tests): void {
    $plateau = new Plateau();

    $tests->assertThrows(fn () => $plateau->calculateNextPosition(56, 2), 'deplacement doit pas depasser le centre');
});
// ===========================================================
// ===========================================================


$tests->test('GESTIONNAIRE : EVALUATION DU DEPLACEMENT', function () use ($tests): void {
    $gestionnaire = new GestionnaireMouvement();
    $plateau = new Plateau();
    $john = new Joueur(2, 'JOHN', COLOR::ROUGE);

    $redJohn1 = $john->getPionById(1); $redJohn2 = $john->getPionById(2);
    if ($redJohn1 === null && $redJohn2 === null) { throw new RuntimeException("PION EST INTROUVABLE"); }

    $redJohn1->startZone(); $redJohn1->moveTo(52);
    $redJohn2->startZone(); $redJohn2->moveTo(52);
    $pg = $plateau->positionToPG($redJohn1->getPosition(), $redJohn1->getColor());
    $redCaseA = $plateau->getCase($pg);
    if ($redJohn1->getState() !== ETAT_PION::ETAT_ARRIVEE) { throw new RuntimeException("PION DEVRAIT ETRE DANS LA VOIE D'ARRIVEE"); }

    $redCaseA->addPawn($redJohn1);

    $evaluation1 = $gestionnaire->evaluateMove($redJohn1, 4, $plateau);
    $tests->assertTrue($evaluation1['valide'], 'EVALUATION DEPLACEMENT possible');
    $tests->assertSame(56, $evaluation1['details']['nouvellePos'], 'nouvelle position vérifiée');

    $redCaseA->addPawn($redJohn2);
    $evaluation2 = $gestionnaire->evaluateMove($redJohn1, 4, $plateau);
    $tests->assertTrue(!$evaluation2['valide'], 'EVALUATION DEPLACEMENT impossible');
});

$tests->test('GESTIONNAIRE : EXECUTION DU DEPLACEMENT', function () use ($tests): void {
    $gestionnaire = new GestionnaireMouvement();
    $plateau = new Plateau();
    $billy = new Joueur(4, 'BILLY', COLOR::JAUNE);

    $yelBilly1 = $billy->getPionById(1); $yelBilly2 = $billy->getPionById(2);
    if ($yelBilly1 === null && $yelBilly2 === null) { throw new RuntimeException("PION EST INTROUVABLE"); }

    $yelBilly1->startZone();

    $pg1 = $plateau->positionToPG($yelBilly1->getPosition(), $yelBilly1->getColor());
    $startCase = $plateau->getCase($pg1); $startCase->addPawn($yelBilly1);

    $execution = $gestionnaire->executeMove($yelBilly1, 5, $plateau);
    $tests->assertTrue($execution['succes'], 'EXECUTION POSSIBLE');

    $pg2 = $plateau->positionToPG($yelBilly1->getPosition(), $yelBilly1->getColor());
    $destination = $plateau->getCase($pg2);

    $tests->assertTrue($destination->isPawnActive($yelBilly1), 'PION sur case destination');

    $yelBilly2->putOnStart(5);
    $destination->addPawn($yelBilly2);

    $execution1 = $gestionnaire->executeMove($yelBilly1, 5, $plateau);
    $tests->assertTrue(!$execution1['succes'], 'EXECUTION IMPOSSIBLE');
});

$tests->test('GESTIONNAIRE : capture toutes la pile adverse', function () use ($tests): void {
    $plateau = new Plateau(); $gestionnaire = new GestionnaireMouvement();
    $redPion1 = new Pion(1, COLOR::ROUGE); $redPion1->putOnStart(1);
    $plateau->getCase(1)->addPawn($redPion1);

    $bluePion1 = new Pion(1, COLOR::BLEU); $bluePion2 = new Pion(2, COLOR::BLEU);
    $bluePion1->putOnStart(41); $bluePion2->putOnStart(41);

    $plateau->getCase(2)->addPawn($bluePion1);
    $plateau->getCase(2)->addPawn($bluePion2);

    $execution = $gestionnaire->executeMove($redPion1, 1, $plateau);
    $tests->assertSame(true, $execution['succes'], 'deplacement avec capture doit réussir');
    $tests->assertSame(ETAT_PION::ETAT_OTAGE, $bluePion1->getState(), 'pion BLEU supérieur doit être capturé');
    $tests->assertSame(ETAT_PION::ETAT_OTAGE, $bluePion2->getState(), 'pion BLEU inférieur doit être capturé');
    $tests->assertSame(COLOR::ROUGE, $bluePion1->getDetenuPar(), 'pion BLEU supérieur doit être détenu par ROUGE');
    $tests->assertSame(COLOR::ROUGE, $bluePion2->getDetenuPar(), 'pion BLEU inférieur doit être détenu par ROUGE');

    $targetCase = $plateau->getCase(2);
    $tests->assertSame(1, $targetCase->countPawns(), 'pile adverse doit être retirée par ce pion');
    $tests->assertSame($redPion1, $targetCase->getTopPawn(), 'pion ROUGE doit occuper la case');
    $tests->assertSame(2, count($execution['details']['capturesEffectuees']), 'nombre de pions capturés');
});

echo "<br/>";
echo "NOTE : les règles métier du Gestionnaire (sorties, IG, priorité des dés) ne sont pas encore exécutées par ce fichier. <br/>Elles doivent être ajoutées avec une API déterministe du Gestionnaire.";
echo "<br/>";

$tests->finish();
?>