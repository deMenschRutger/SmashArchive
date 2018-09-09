<?php

declare(strict_types = 1);

namespace App\Bracket\DoubleElimination;

use App\Bracket\SingleElimination\RanksGenerator as SingleEliminationRanksGenerator;
use App\Entity\Entrant;
use App\Entity\Event;
use App\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RanksGenerator extends SingleEliminationRanksGenerator
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
     *
     * @return array
     */
    public function getRanks(Event $event)
    {
        if (count($this->ranks) > 0) {
            return $this->ranks;
        }

        $winnersBracket = $this->bracket->getIterableBracket();
        $losersBracket = $this->bracket->getIterableLosersBracket();

        $this->processBracket($event, $winnersBracket);
        $this->processBracket($event, $losersBracket);

        $grandFinalsSets = $this->bracket->getGrandFinalsSets();
        $this->processGrandFinalsSets($event, $grandFinalsSets);

        $this->sortRanks();

        return $this->ranks;
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

        $this->addRank($event, $winner, 1);
        $this->addRank($event, $loser, 2);

        $this->processGrandFinalsSets($event, $sets);
    }
}
