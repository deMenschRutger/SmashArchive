<?php

declare(strict_types = 1);

namespace App\Bracket\SingleElimination;

use App\Bracket\AbstractRanksGenerator;
use App\Entity\Entrant;
use App\Entity\Event;
use App\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RanksGenerator extends AbstractRanksGenerator
{
    /**
     * @var int
     */
    protected $winnerRank = 1;

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

        $bracket = $this->bracket->getIterableBracket();

        $this->processBracket($event, $bracket);
        $this->sortRanks();

        return $this->ranks;
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
                if ($set->isOrphaned()) {
                    continue;
                }

                $entrantOne = $set->getEntrantOne();
                $entrantTwo = $set->getEntrantTwo();
                $rank = $set->getLoserRank();

                if ($entrantOne instanceof Entrant) {
                    $this->addRank($event, $entrantOne, $rank);
                }

                if ($entrantTwo instanceof Entrant) {
                    $this->addRank($event, $entrantTwo, $rank);
                }

                if (!$set->isFinals()) {
                    continue;
                }

                $winner = $set->getWinner();

                if ($winner instanceof Entrant) {
                    $this->addRank($event, $winner, $this->winnerRank);
                }
            }
        }
    }
}
