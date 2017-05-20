<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
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
     * @var int
     */
    protected $roundsRequired;

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
     * @return int
     */
    public function countEntrants()
    {
        $entrants = $this->phaseGroup->getEntrants();

        return count($entrants);
    }

    /**
     * @return float
     */
    public function getRoundsRequired()
    {
        if (!$this->roundsRequired) {
            $this->roundsRequired = ceil(log($this->countEntrants(), 2));
        }

        return $this->roundsRequired;
    }

    /**
     * Only applies to single and double elimination brackets. Should be overwritten by other bracket types.
     *
     * @return float
     */
    public function getBracketSize()
    {
        return pow(2, $this->getRoundsRequired());
    }

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
            if (!$set->getEntrantOne() instanceof Entrant &&
                !$set->getEntrantTwo() instanceof Entrant
            ) {
                continue;
            }

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
