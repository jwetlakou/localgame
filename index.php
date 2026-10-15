<?php
declare(strict_types=1);

require_once __DIR__ . '/src/pions.php';
require_once __DIR__ . '/src/pawnstack.php';
require_once __DIR__ . '/src/cases.php';
require_once __DIR__ . '/src/plateau.php';
require_once __DIR__ . '/src/des.php';
require_once __DIR__ . '/src/joueur.php';

use App\COLOR;
use App\ETAT_PION;
use App\TYPE_CASE;
use App\Pion;
use App\PawnStack;
use App\Cases;
use App\Plateau;
use App\Des;
use App\Joueur;

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

$tests->test('PION : sortie au départ', function () use ($tests): void {
    $pion = new Pion(1, COLOR::BLEU);
    $pion->startZone();

    $tests->assertSame(ETAT_PION::ETAT_CIRCUIT, $pion->getState(), 'état après sortie');
    $tests->assertSame(0, $pion->getPosition(), 'position après sortie');
});

$tests->test('PION : ARRIVEE et CENTRE', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    $pion->startZone();
    $pion->moveTo(52);
    
    $tests->assertSame(ETAT_PION::ETAT_ARRIVEE, $pion->getState(), 'état arrivée');

    $pion->moveTo(57);

    $tests->assertSame(ETAT_PION::ETAT_CENTRE, $pion->getState(), 'état centre');
    $tests->assertSame(57, $pion->getPosition(), 'position centre');
});

$tests->test('PION : mouvement impossible depuis MAISON', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    
    $tests->assertThrows(fn()=> $pion->moveTo(3), 'un pion en maison doit pas pouvoir se deplacer normalement');
});

$tests->test('PION : capture & libération', function () use ($tests): void {
    $pion = new Pion(1, COLOR::ROUGE);
    $pion->startZone();
    $pion->takenBy(COLOR::BLEU);
    
    $tests->assertSame(ETAT_PION::ETAT_OTAGE, $pion->getState(), 'pion doit être en OTAGE');
    $tests->assertSame(COLOR::BLEU, $pion->getDetenuPar(), 'detenteur non null');
    
    $pion->release();
    $tests->assertSame(ETAT_PION::ETAT_MAISON, $pion->getState(), 'pion libéré doit revenir en MAISON');
    $tests->assertSame(null, $pion->getDetenuPar(), 'detenteur doit être effacé');
});
// ===========================================================
// ===========================================================

$tests->test('PILE : ordre LIFO', function () use ($tests): void {
    $pile = new PawnStack();
    $pion1 = new Pion(1, COLOR::ROUGE);
    $pion2 = new Pion(2, COLOR::ROUGE);
    
    $pile->push($pion1);
    $pile->push($pion2);

    $tests->assertSame($pion2, $pile->peek(), 'dernier pion ajouté doit être au sommet');
    $tests->assertSame($pion2, $pile->pop(), 'dernier pion ajouté doit être sortir en premier');
    $tests->assertSame($pion1, $pile->pop(), 'premier pion ajouté doit sortir ensuite');
    $tests->assertTrue($pile->isEmpty(), 'pile doit être vide');
});

$tests->test('PILE : ordre LIFO', function () use ($tests): void {
    $pile = new PawnStack();
    $tests->assertThrows(fn() => $pile->pop(), 'retirer un pion d\'une pile');
});
// ===========================================================
// ===========================================================


$tests->test('CASES : depart accepte plusieur couleurs', function () use ($tests): void {
    $depart = new Cases(13, TYPE_CASE::DEPART, COLOR::BLEU);

    $rouge = new Pion(1, COLOR::ROUGE);
    $bleu = new Pion(1, COLOR::BLEU);
    $jaune = new Pion(1, COLOR::JAUNE);
    $vert = new Pion(1, COLOR::VERT);

    $depart->addPawn($rouge);
    $depart->addPawn($bleu);
    $depart->addPawn($jaune);
    $depart->addPawn($vert);

    $tests->assertSame(4, $depart->countPawns(), 'pile du depart doit contenir 3 pions');
    $tests->assertTrue($depart->isPawnActive($vert), 'dernier pion ajouté doit être actif');
    $tests->assertTrue(!$depart->isPawnActive($rouge), 'pion inférieur doit être inactif');
});

$tests->test('CASES : NEUTRE accepte une pile de même couleur', function () use ($tests): void {
    $case = new Cases(4);

    $pion1 = new Pion(1, COLOR::ROUGE);
    $pion2 = new Pion(2, COLOR::ROUGE);

    $case->addPawn($pion1);
    $case->addPawn($pion2);

    $tests->assertSame(2, $case->countPawns(), 'deux pions de même couleur');
});

$tests->test('CASES : NEUTRE refuse un cinquième pion', function () use ($tests): void {
    $case = new Cases(4);
    for ($id = 1; $id <= 4; $id++) { $case->addPawn(new Pion ($id, COLOR::ROUGE)); }
    $tests->assertThrows(fn() => $case->addPawn(new Pion(5, COLOR::ROUGE)), 'case NEUTRE doit pas accepter 5e pion');
});

$tests->test('CASES : ARRIVEE refuse un cinquième pion', function () use ($tests): void {
    $arrivee = new Cases(52, TYPE_CASE::ARRIVEE, COLOR::ROUGE);
    $tests->assertThrows(fn() => $arrivee->addPawn(new Pion(1, COLOR::BLEU)), 'case ARRIVEE doit pas accepter pion BLEU');
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

echo "<br/>";
echo "NOTE : les règles métier du Gestionnaire (sorties, IG, priorité des dés) ne sont pas encore exécutées par ce fichier. <br/>Elles doivent être ajoutées avec une API déterministe du Gestionnaire.";
echo "<br/>";

$tests->finish();
?>
