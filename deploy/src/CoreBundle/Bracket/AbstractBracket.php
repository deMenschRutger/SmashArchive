<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
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
    }

    /**
     * @return array
     */
    abstract public function getIterableBracket();

    /**
     * @return AbstractResultsGenerator
     */
    abstract public function getResultsGenerator();

    /**
     * @return Entrant[]
     */
    public function getEntrants()
    {
        return $this->phaseGroup->getEntrants();
    }

    /**
     * @return int
     */
    public function countEntrants()
    {
        return count($this->getEntrants());
    }

    /**
     * @return int
     */
    public function getRoundsRequired()
    {
        if (!$this->roundsRequired) {
            $this->roundsRequired = ceil(log($this->countEntrants(), 2));
        }

        return intval($this->roundsRequired);
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
     * @param Event $event
     * @return Result[]
     */
    public function getResults(Event $event)
    {
        return $this->getResultsGenerator()->getResults($event);
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
     * The purpose of this function is to remove any round that ended up with only unplayed sets, but only at the beginning and end
     * of the bracket.
     *
     * @param array $bracket
     * @return array
     */
    protected function cleanBracket($bracket)
    {
        foreach ($bracket as $round => $sets) {
            /** @var Set $set */
            foreach ($sets as $set) {
                if ($set->getStatus() !== Set::STATUS_NOT_PLAYED) {
                    break 2;
                }
            }

            unset($bracket[$round]);
        }

        foreach (array_reverse(array_keys($bracket)) as $round) {
            $sets = $bracket[$round];

            /** @var Set $set */
            foreach ($sets as $set) {
                if ($set->getStatus() !== Set::STATUS_NOT_PLAYED) {
                    break 2;
                }
            }

            unset($bracket[$round]);
        }

        return $bracket;
    }
}
