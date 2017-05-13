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
     * @var array
     */
    protected $setsByRound = [];

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function __construct(PhaseGroup $phaseGroup)
    {
        $this->phaseGroup = $phaseGroup;
        $this->init();
    }

    /**
     * @return array
     */
    public function getRounds()
    {
        return array_values($this->rounds);
    }

    /**
     * @param int $round
     * @return array
     */
    public function getSetsByRound($round)
    {
        if (!array_key_exists($round, $this->setsByRound)) {
            throw new \InvalidArgumentException("Round number {$round} does not exist in this bracket.");
        }

        return $this->setsByRound[$round];
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

            if (!array_key_exists($round, $this->setsByRound)) {
                $this->setsByRound[$round] = [];
            }

            $this->setsByRound[$round][] = $set;
        }
    }

    /**
     * @param Set $set
     * @return int
     */
    abstract protected function getMappedRound(Set $set);
}
