<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\RoundRobin;

use CoreBundle\Bracket\AbstractBracket;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Bracket extends AbstractBracket
{
    /**
     * @var array
     */
    protected $iterableBracket = [];

    /**
     * @return ResultsGenerator
     */
    public function getResultsGenerator()
    {
        if (!$this->resultsGenerator instanceof ResultsGenerator) {
            $this->resultsGenerator = new ResultsGenerator($this);
        }

        return $this->resultsGenerator;
    }

    /**
     * @return int
     */
    public function getRoundsRequired()
    {
        return array_sum(range(1, $this->countEntrants() - 1));
    }

    /**
     * @return Entrant[]
     */
    public function getEntrants()
    {
        return $this->phaseGroup->getEntrants();
    }

    /**
     * @return array
     */
    public function getIterableBracket()
    {
        if (count($this->iterableBracket) > 0) {
            return $this->iterableBracket;
        }

        $entrants = $this->phaseGroup->getEntrants();

        while (count($entrants) > 0) {
            $entrant = array_shift($entrants);

            /** @var Entrant $opponent */
            foreach ($entrants as $opponent) {
                $set = new Set();
                $set->setStatus(Set::STATUS_NOT_PLAYED);
                $set->setRound(1);
                $set->setEntrantOne($entrant);
                $set->setEntrantTwo($opponent);

                $tag = $entrant->getId().'-'.$opponent->getId();
                $reverseTag = $opponent->getId().'-'.$entrant->getId();

                $this->iterableBracket[$tag] = $set;
                $this->iterableBracket[$reverseTag] = $set;
            }
        }

        $this->matchSets();

        return $this->iterableBracket;
    }

    /**
     * @param Entrant $entrantOne
     * @param Entrant $entrantTwo
     * @return Set
     */
    public function getSet(Entrant $entrantOne, Entrant $entrantTwo)
    {
        $bracket = $this->getIterableBracket();
        $setTag = $entrantOne->getId().'-'.$entrantTwo->getId();

        if (array_key_exists($setTag, $bracket)) {
            return $bracket[$setTag];
        }

        return new Set();
    }

    /**
     * @return array
     */
    protected function generateVirtualBracket()
    {
        return [];
    }

    /**
     * @return void
     */
    protected function matchSets()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $tag = $set->getTag();
            $reverseTag = $set->getTag(true);

            if (array_key_exists($tag, $this->iterableBracket)) {
                /** @var Set $matchedSet */
                $this->iterableBracket[$tag] = $set;
            }

            if (array_key_exists($reverseTag, $this->iterableBracket)) {
                /** @var Set $matchedSet */
                $this->iterableBracket[$reverseTag] = $set;
            }
        }
    }
}
