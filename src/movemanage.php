<?php
declare(strict_types=1);
namespace App;

use InvalidArgumentException;
use LogicException;

final class GestionnaireMouvement {
    public function isPionActif (Pion $pion, Plateau $plateau): bool {
        $state = $pion->getState();
        if ($state !== ETAT_PION::ETAT_CIRCUIT && $state !== ETAT_PION::ETAT_ARRIVEE) { return false; }

        try {
            $pg = $plateau->positionToPG($pion->getPosition(), $pion->getColor());
            $case = $plateau->getCase($pg);
        } catch (InvalidArgumentException) { return false; }

        return $case->isPawnActive($pion);
    }

    public function evaluateMove (Pion $pion, int $diceValue, Plateau $plateau): array {
        if ($diceValue < 1 || $diceValue > 6) { return $this->refuse('VALEUR DU DÉ DOIT ETRE COMPRISE ENTRE 1 ET 6'); }
        
        $actualState = $pion->getState();
        if ($actualState !== ETAT_PION::ETAT_CIRCUIT && $actualState !== ETAT_PION::ETAT_ARRIVEE) { return $this->refuse('PION DOIT ETRE SUR LE CIRCUIT OU VOIE D\'ARRIVEE'); }
        if (!$this->isPionActif($pion, $plateau)) { return $this->refuse('PION ABSENT DE SA CASE OU RECOUVERT PAR UN AUTRE PION'); }

        $oldPosition = $pion->getPosition();

        try {
            $newPosition = $plateau->calculateNextPosition($oldPosition, $diceValue);
            $oldPG = $plateau->positionToPG($oldPosition, $pion->getColor());
            $newPG = $plateau->positionToPG($newPosition, $pion->getColor());
            $targetTile = $plateau->getCase($newPG);
        } catch (InvalidArgumentException) { return $this->refuse('DEPLACEMENT DEPASSE UNE LIMITE AUTORISEE DU PARCOURS'); }

        $captureRequired = false;
        $capturedPawns = [];
        
        if ($targetTile->getType() === TYPE_CASE::NEUTRE && !$targetTile->isEmpty()) {
            $topPawn = $targetTile->getTopPawn();
            
            if ($topPawn !== null && $topPawn->getColor() !== $pion->getColor()) {
                $captureRequired = true;
                $capturedPawns = $targetTile->getPawns();

                foreach ($capturedPawns as $opponent) {
                    if ($opponent->getState() !== ETAT_PION::ETAT_CIRCUIT) { return $this->refuse('CAPTURE REFUSÉE - ÉTAT INCOHÉRENT DANS LA PILE ADVERSE'); }
                    if ($opponent->getColor() === $pion->getColor()) { return $this->refuse('CAPTURE REFUSÉE - PILE DE COULEURS INCOHÉRENTES'); }
                }
            }
        }

        if (!$captureRequired && !$targetTile->canAcceptPawns($pion)) { return $this->refuse('CASE DE DESITINATION PEUT PAS ACCUEILLIR CE PION'); }

        $newState = match (true) { $newPosition <= 51 => ETAT_PION::ETAT_CIRCUIT, $newPosition <= 56 => ETAT_PION::ETAT_ARRIVEE, default => ETAT_PION::ETAT_CENTRE };
        
        return [
            'valide'    => true,
            'message'   => $captureRequired ? 'DÉPLACEMENT GÉOMÉTRIQUEMENT VALIDE... CAPTURE DOIT ETRE TRAITÉE' : 'DÉPLACEMENT VALIDE',
            'details'   => [
                'pionID'        => $pion->getId(),
                'couleur'       => $pion->getColor()->value,
                'anciennePos'   => $oldPosition,
                'nouvellePos'   => $newPosition,
                'anciennePG'    => $oldPG,
                'nouvellePG'    => $newPG,
                'nouvelEtat'    => $newState,
                'captureRequise'=> $captureRequired,
                'pionsCaptures' => array_map(static fn (Pion $opponent): array => [
                    'id'        => $opponent->getId(),
                    'couleur'   => $opponent->getColor()->value
                ], $capturedPawns)
            ]
        ];
    }

    public function executeMove (Pion $pion, int $diceValue, Plateau $plateau): array {
        $evaluation = $this->evaluateMove($pion, $diceValue, $plateau);
        if (!$evaluation['valide']) { return ['succes' => false, 'message' => $evaluation['message']]; }

        $details = $evaluation['details'];

        $actualCase = $plateau->getCase($details['anciennePG']);
        $targetTile = $plateau->getCase($details['nouvellePG']);

        if (!$actualCase->isPawnActive($pion)) { return $this->failExecution('PION EST INACTIF SUR SA CASE DE DEPART'); }
        $captureRequired = $details['captureRequise'];
        $opponents = [];

        if ($captureRequired) {
            $opponents = $targetTile->getPawns();
            if ($opponents === []) { return $this->failExecution('CAPTURE ANNULÉE - PILE ADVERSE EST VIDE'); }

            foreach ($opponents as $opponent) {
                if ($opponent->getState() !== ETAT_PION::ETAT_CIRCUIT || $opponent->getColor() === $pion->getColor()) { return $this->failExecution('CAPTURE ANNULÉE - PILE ADVERSE A CHANGÉ'); }
            }
        } elseif (!$targetTile->canAcceptPawns($pion)) { return $this->failExecution('DESTINATION N\'ACCEPTE PLUS CE PION'); }

        $capturesExecuted = [];
        if ($captureRequired) {
            $removedPawns = $targetTile->clear();
            
            foreach ($removedPawns as $opponent) {
                $opponent->takenBy($pion->getColor());
                
                $capturesExecuted[] = [
                    'id'     => $opponent->getId(),
                    'couleur'=> $opponent->getColor()->value,
                    'etat'   => $opponent->getState()->value,
                    'detenuPar'=> $opponent->getDetenuPar()?->value,
                ];
            }
        }

        $removePawn = $actualCase->removeTopPawn();
        if ($removePawn !== $pion) { throw new LogicException("INCOHÉRENCE DU PLATEAU - PION RETIRÉ NE CORRESPOND PAS AU PION ACTIF"); }

        $pion->moveTo($details['nouvellePos']);
        $targetTile->addPawn($pion);

        return [
            'succes'    => true,
            'message'   => "DEPLACEMENT EXÉCUTÉ",
            'details'   => [
                ...$details,
                'nouvelEtat'    => $pion->getState()->value,
                'capturesEffectuees' => $capturesExecuted
            ]
        ];
    }

    private function refuse (string $message): array {
        return ['valide' => false, 'message' => $message];
    }

    private function failExecution (string $message): array {
        return ['succes' => false, 'message' => $message];
    }
}
?>