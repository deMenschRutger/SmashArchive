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
     * @var int
     */
    protected $winnerRank = 1;

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
                'win' => 0,
                'lose' => $useScores ? 0 : null,
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
                $this->scores[$loserId]['lose'] += $set->getLoserScore();
            } else {
                $this->scores[$winnerId]['win']++;
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
        return [];
    }
}
