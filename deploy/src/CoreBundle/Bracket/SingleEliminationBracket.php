<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SingleEliminationBracket extends AbstractBracket
{
    /**
     * @var array
     */
    protected $setsById = [];

    /**
     * @var array
     */
    protected $setsByRound = [];

    /**
     * @return void
     */
    protected function init()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $setId = $set->getId();
            $round = $set->getRound();

            $this->setsById[$setId] = $set;
            $this->setsByRound[$round][] = $set;
        }

        // Make sure the rounds are in the right order.
        ksort($this->setsByRound);

        // Reset the indexes in case certain round numbers were skipped for some reason.
        $this->setsByRound = array_values($this->setsByRound);

        $totalRounds = count($this->setsByRound);

        foreach ($this->setsById as $set) {
            $this->determineIsGrandFinals($set, $totalRounds);
        }
    }

    /**
     * @param Set $set
     * @param int $totalRounds
     */
    protected function determineIsGrandFinals(Set $set, int $totalRounds)
    {
        $lastRoundIndex = $totalRounds - 1;
        $lastRoundSets = $this->setsByRound[$lastRoundIndex];

        if (in_array($set, $lastRoundSets)) {
            $set->setIsGrandFinals(true);
            $set->setRoundName('Grand Finals');
        } else {
            $set->setRoundName('Not Grand Finals');
        }
    }
}