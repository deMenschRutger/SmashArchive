<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\RoundRobin;

use CoreBundle\Bracket\AbstractResultsGenerator;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends AbstractResultsGenerator
{
    /**
     * @var array
     */
    protected $scores = [];

    /**
     * @return array
     */
    public function getScores()
    {
        if (count($this->scores) > 0) {
            return $this->scores;
        }

        $sets = array_unique($this->bracket->getIterableBracket());
        $useScores = true;

        /** @var Set $set */
        foreach ($sets as $set) {
            if (!$set->hasResultWithScore()) {
                $useScores =  false;

                break;
            }
        }

        foreach ($this->bracket->getEntrants() as $entrant) {
            $this->scores[$entrant->getId()] = [
                'entrant' => $entrant,
                'win' => 0,
                'lose' => $useScores ? 0 : null,
                'total' => 0,
            ];
        }

        foreach ($sets as $set) {
            if (!$set->hasResult()) {
                continue;
            }

            $winnerId = $set->getWinnerId();
            $loserId = $set->getLoserId();

            if ($useScores) {
                $this->scores[$winnerId]['win'] += $set->getWinnerScore();
                $this->scores[$winnerId]['lose'] += $set->getLoserScore();
                $this->scores[$winnerId]['total'] += $set->getWinnerScore() - $set->getLoserScore();

                $this->scores[$loserId]['win'] += $set->getLoserScore();
                $this->scores[$loserId]['lose'] += $set->getWinnerScore();
                $this->scores[$loserId]['total'] += $set->getLoserScore() - $set->getWinnerScore();
            } else {
                $this->scores[$winnerId]['win']++;
                $this->scores[$winnerId]['total']++;
            }
        }

        return $this->scores;
    }

    /**
     * @param Event $event
     * @return array
     */
    public function getResults(Event $event)
    {
        if (count($this->results) > 0) {
            return $this->results;
        }

        $scores = $this->getScores();

        usort($scores, function (array $scoreA, array $scoreB) {
            if ($scoreA['total'] === $scoreB['total']) {
                return 0;
            }

            return $scoreA['total'] < $scoreB['total'];
        });

        $previousScore = null;
        $rank = 1;
        $buildUp = 0;

        foreach ($scores as $score) {
            if ($score['total'] !== $previousScore) {
                $rank += $buildUp;
                $buildUp = 0;
            }

            $this->addResult($event, $score['entrant'], $rank);

            $buildUp++;
            $previousScore = $score['total'];
        }

        return $this->results;
    }
}
