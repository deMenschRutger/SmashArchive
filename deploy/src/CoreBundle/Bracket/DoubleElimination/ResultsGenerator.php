<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\SingleElimination\ResultsGenerator as SingleEliminationResultsGenerator;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends SingleEliminationResultsGenerator
{
    /**
     * @var int
     */
    protected $winnerRank = 2;

    /**
     * @var Bracket
     */
    protected $bracket;

    /**
     * @param Event $event
     * @return array
     */
    public function getResults(Event $event)
    {
        if (count($this->results) > 0) {
            return $this->results;
        }

        $winnersBracket = $this->bracket->getIterableBracket();
        $losersBracket = $this->bracket->getIterableLosersBracket();

        $this->processBracket($event, $winnersBracket);
        $this->processBracket($event, $losersBracket);

        $grandFinalsSets = $this->bracket->getGrandFinalsSets();
        $this->processGrandFinalsSets($event, $grandFinalsSets);

        $this->sortResults();

        return $this->results;
    }

    /**
     * @param Event $event
     * @param array $sets
     */
    protected function processGrandFinalsSets(Event $event, array $sets)
    {
        if (count($sets) === 0) {
            return;
        }

        $set = array_shift($sets);

        if (!$set instanceof Set) {
            return;
        }

        if ($set->wasNotPlayed()) {
            $this->processGrandFinalsSets($event, $sets);

            return;
        }

        $winner = $set->getWinner();
        $loser = $set->getLoser();

        if (!$winner instanceof Entrant || !$loser instanceof Entrant) {
            return;
        }

        $this->addResult($event, $winner, 1);
        $this->addResult($event, $loser, 2);

        $this->processGrandFinalsSets($event, $sets);
    }
}
