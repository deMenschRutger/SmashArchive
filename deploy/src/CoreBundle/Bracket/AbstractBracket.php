<?php

declare(strict_types = 1);

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
    protected $rounds = [];

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function __construct(PhaseGroup $phaseGroup)
    {
        $this->phaseGroup = $phaseGroup;
        $this->init();
    }

    /**
     * @param Set $set
     */
    public function determineRoundName(Set $set)
    {
        $mappedRound = $this->getMappedRound($set);

        if (!$mappedRound) {
            return;
        }

        $set->setRoundName($mappedRound['name']);
    }

    /**
     * @param Set $set
     */
    public function determineIsGrandFinals(Set $set)
    {
        $mappedRound = $this->getMappedRound($set);

        if (!$mappedRound) {
            return;
        }

        $set->setIsGrandFinals($mappedRound['isGrandFinals']);
    }

    /**
     * @return void
     */
    protected function init()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            if (!$set->getEntrantOne() instanceof Entrant ||
                !$set->getEntrantTwo() instanceof Entrant
            ) {
                continue;
            }

            $round = $set->getRound();
            $this->rounds[$round] = $round;
        }
    }

    /**
     * @param Set $set
     * @return int
     */
    abstract protected function getMappedRound(Set $set);
}