<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Result;
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
     * @var AbstractResultsGenerator
     */
    protected $resultsGenerator;

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
        $this->processSets();
        $this->generateBracket();
    }

    /**
     * @return AbstractResultsGenerator
     */
    abstract public function getResultsGenerator();

    /**
     * @return array
     */
    public function getRounds()
    {
        return array_keys($this->setsByRound);
    }

    /**
     * @param int $round
     * @return array
     */
    public function getSetsForRound($round)
    {
        if (!array_key_exists($round, $this->setsByRound)) {
            throw new \InvalidArgumentException("Round number {$round} does not exist in this bracket.");
        }

        return $this->setsByRound[$round];
    }

    /**
     * @return Result[]
     */
    public function getResults()
    {
        return $this->getResultsGenerator()->getResults();
    }

    /**
     * @return void
     */
    protected function processSets()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $round = $set->getRound();

            if (!array_key_exists($round, $this->setsByRound)) {
                $this->setsByRound[$round] = [];
            }

            $this->setsByRound[$round][] = $set;
        }

        ksort($this->setsByRound);
    }

    /**
     * @return void
     */
    abstract protected function generateBracket();
}
