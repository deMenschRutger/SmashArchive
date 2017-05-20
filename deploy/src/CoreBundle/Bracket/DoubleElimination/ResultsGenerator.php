<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\SingleElimination\ResultsGenerator as SingleEliminationResultsGenerator;
use CoreBundle\Entity\Event;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends SingleEliminationResultsGenerator
{
    /**
     * @var Bracket
     */
    protected $bracket;

    /**
     * @param Event $event
     * @return array
     *
     * @TODO Process the grand finals.
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
        $this->sortResults();

        return $this->results;
    }
}
