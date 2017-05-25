<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\SingleElimination;

use CoreBundle\Bracket\AbstractResultsGenerator;
use CoreBundle\Entity\Entrant;
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
     * @param Event $event
     * @return array
     */
    public function getResults(Event $event)
    {
        if (count($this->results) > 0) {
            return $this->results;
        }

        $bracket = $this->bracket->getIterableBracket();

        $this->processBracket($event, $bracket);
        $this->sortResults();

        return $this->results;
    }

    /**
     * @param Event $event
     * @param array $bracket
     */
    protected function processBracket(Event $event, array $bracket)
    {
        foreach ($bracket as $round => $sets) {
            /** @var Set $set */
            foreach ($sets as $set) {
                $entrantOne = $set->getEntrantOne();
                $entrantTwo = $set->getEntrantTwo();
                $rank = $set->getLoserRank();

                if ($entrantOne instanceof Entrant) {
                    $this->addResult($event, $entrantOne, $rank);
                }

                if ($entrantTwo instanceof Entrant) {
                    $this->addResult($event, $entrantTwo, $rank);
                }

                if (!$set->isFinals()) {
                    continue;
                }

                $winner = $set->getWinner();

                if ($winner instanceof Entrant) {
                    $this->addResult($event, $winner, $this->winnerRank);
                }
            }
        }
    }
}
