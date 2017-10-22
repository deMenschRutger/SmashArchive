<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Challonge\Processor;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Set;
use CoreBundle\Importer\AbstractProcessor;
use Reflex\Challonge\Models\Match;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetProcessor extends AbstractProcessor
{
    /**
     * @var Set[]
     */
    protected $sets = [];

    /**
     * @param int $setId
     * @return bool
     */
    public function hasSet($setId)
    {
        return array_key_exists($setId, $this->sets);
    }

    /**
     * @param int $setId
     * @return Set
     */
    public function findSet($setId)
    {
        if ($this->hasSet($setId)) {
            return $this->sets[$setId];
        }

        return null;
    }

    /**
     * @param Match            $setData
     * @param EntrantProcessor $entrantProcessor
     * @param PhaseGroup       $phaseGroup
     */
    public function processNew(Match $setData, EntrantProcessor $entrantProcessor, PhaseGroup $phaseGroup = null)
    {
        $setId = $setData->id;

        if ($this->hasSet($setId)) {
            return;
        }

        $set = $this->entityManager->getRepository('CoreBundle:Set')->findOneBy([
            'smashggId' => $setId,
        ]);

        if (!$set instanceof Set) {
            $set = new Set();
            $set->setSmashggId($setId);

            $this->entityManager->persist($set);
        }

        $set->setRound($setData->round);

        if ($phaseGroup instanceof PhaseGroup) {
            $set->setPhaseGroup($phaseGroup);
        }

        $entrantOne = $entrantProcessor->findEntrant($setData->player1_id);
        $entrantTwo = $entrantProcessor->findEntrant($setData->player2_id);

        if ($entrantOne) {
            $set->setEntrantOne($entrantOne);
        }

        if ($entrantTwo) {
            $set->setEntrantTwo($entrantTwo);
        }

        $entrant1Score = null;
        $entrant2Score = null;
        $processedScores = $this->processScores($setData->scores_csv);

        if ($processedScores) {
            $entrant1Score = intval($processedScores[1]);
            $entrant2Score = intval($processedScores[2]);
        }

        if ($setData->winner_id && $setData->winner_id == $setData->player1_id) {
            $set->setWinner($entrantOne);
            $set->setWinnerScore($entrant1Score);
            $set->setLoser($entrantTwo);
            $set->setLoserScore($entrant2Score);
        } elseif ($setData->winner_id && $setData->winner_id == $setData->player2_id) {
            $set->setWinner($entrantTwo);
            $set->setWinnerScore($entrant2Score);
            $set->setLoser($entrantOne);
            $set->setLoserScore($entrant1Score);
        }

        if ($set->getEntrantOne() instanceof Entrant && $set->getEntrantTwo() instanceof Entrant && $set->getLoserScore() === -1) {
            $set->setStatus(Set::STATUS_DQED);
            $set->setIsRanked(false);
        } elseif ($set->getWinner() === null && $set->getLoser() === null) {
            $set->setStatus(Set::STATUS_NOT_PLAYED);
            $set->setIsRanked(false);
        } elseif ($set->getWinner() instanceof Entrant && $set->getLoser() === null) {
            $set->setStatus(Set::STATUS_NOT_PLAYED);
            $set->setIsRanked(false);
        }

        $this->sets[$setId] = $set;
    }

    /**
     * @param string $score
     * @return array|false
     */
    protected function processScores($score)
    {
        $scores = [
            1 => '',
            2 => '',
        ];
        $activePlayer = 1;
        $hasScore = false;

        foreach (str_split($score) as $character) {
            if ($character === '-') {
                if ($hasScore) {
                    $activePlayer = 2;
                    $hasScore = false;
                } else {
                    $scores[$activePlayer] = $scores[$activePlayer].$character;
                }
            } else {
                $scores[$activePlayer] = $scores[$activePlayer].$character;
                $hasScore = true;
            }
        }

        if ($scores[1] === '' || $scores[2] === '') {
            return false;
        }

        return $scores;
    }
}
