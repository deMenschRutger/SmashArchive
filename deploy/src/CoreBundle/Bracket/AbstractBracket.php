<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractBracket
{
    /**
     * @var PhaseGroup
     */
    protected $phaseGroup;

    /**
     * @var array
     */
    protected $setsByRound = [];

    /**
     * @var array
     */
    protected $entrants = [];

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function __construct(PhaseGroup $phaseGroup)
    {
        $this->phaseGroup = $phaseGroup;

        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $this->addEntrant($set->getEntrantOne());
            $this->addEntrant($set->getEntrantTwo());

            $round = $set->getRound();
            $this->setsByRound[$round][] = $set;
        }

        // Make sure the rounds are in the right order.
        ksort($this->setsByRound);

        // Reset the indexes in case certain round numbers were skipped for some reason.
        $this->setsByRound = array_values($this->setsByRound);
    }

    /**
     * @param Entrant $entrant
     */
    protected function addEntrant($entrant)
    {
        if (!$entrant instanceof Entrant) {
            return;
        }

        if (!in_array($entrant, $this->entrants, true)) {
            $this->entrants[] = $entrant;
        }
    }

    /**
     * @param Set $set
     * @return int
     */
    protected function getReverseIndex(Set $set)
    {
        $entrantCount = count($this->entrants);
        $totalRounds = ceil(log($entrantCount, 2));

        return intval($totalRounds) - $set->getRound();
    }

    /**
     * @param Set $set
     */
    abstract protected function determineRoundName(Set $set);

    /**
     * @param Set $set
     */
    abstract protected function determineIsGrandFinals(Set $set);
}