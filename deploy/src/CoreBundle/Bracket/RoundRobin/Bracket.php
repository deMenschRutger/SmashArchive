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
                $set->setEntrantOne($entrant);
                $set->setEntrantTwo($opponent);
                $set->setPhaseGroup($this->phaseGroup);
                $set->setRound(1);
                $set->setStatus(Set::STATUS_NOT_PLAYED);

                $tag = $entrant->getId().'-'.$opponent->getId();
                $this->iterableBracket[$tag] = $set;
            }
        }

        return $this->iterableBracket;
    }

    /**
     * @param Entrant $entrantOne
     * @param Entrant $entrantTwo
     * @return int|null|string
     */
    public function getSetScore(Entrant $entrantOne, Entrant $entrantTwo)
    {
        $bracket = $this->getIterableBracket();
        $setTag = $entrantOne->getId().'-'.$entrantTwo->getId();

        if (array_key_exists($setTag, $bracket)) {
            /** @var Set $set */
            $set = $bracket[$setTag];

            if ($set->wasNotPlayed()) {
                return 0;
            }

            return $set->getWinnerScore().' - '.$set->getLoserScore();
        }

        $setTag = $entrantTwo->getId().'-'.$entrantOne->getId();

        if (array_key_exists($setTag, $bracket)) {
            /** @var Set $set */
            $set = $bracket[$setTag];

            if ($set->wasNotPlayed()) {
                return 0;
            }

            return $set->getWinnerScore().' - '.$set->getLoserScore();
        }

        return null;
    }

    /**
     * @return array
     */
    protected function generateVirtualBracket()
    {
        return [];
    }
}
