<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\SingleElimination;

use CoreBundle\Bracket\AbstractBracket;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Bracket extends AbstractBracket
{
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
     * @return array
     */
    public function getIterableBracket()
    {
        if (count($this->getRounds()) === 0) {
            return [];
        }

        $bracket = $this->generateVirtualBracket();
        $roundsRequired = $this->getRoundsRequired();

        foreach (array_reverse($this->getRounds()) as $index => $roundNumber) {
            if ($roundNumber < 0) {
                break;
            }

            $sets = $this->getSetsForRound($roundNumber);
            $bracket = $this->matchSetsForRound($bracket, $roundsRequired - $index, $sets);

            if ($index + 1 >= $roundsRequired) {
                break;
            }
        }

        return $this->cleanBracket($bracket);
    }

    /**
     * @return array
     */
    protected function generateVirtualBracket()
    {
        $roundsRequired = $this->getRoundsRequired();
        $bracket = [];

        for ($round = 1; $round <= $roundsRequired; $round++) {
            $bracket[$round] = $this->generateVirtualRound($round);
        }

        return $bracket;
    }

    /**
     * @param int $roundNumber
     * @return array
     */
    protected function generateVirtualRound($roundNumber)
    {
        $setCount = $this->getBracketSize() / pow(2, $roundNumber);
        $round = [];

        $loserRank = $setCount + 1;
        $isFinals = $setCount == 1;

        for ($i = 1; $i <= $setCount; $i++) {
            $set = new Set();
            $set->setRoundName($this->getRoundName($roundNumber));
            $set->setLoserRank($loserRank);
            $set->setIsFinals($isFinals);
            $set->setStatus(Set::STATUS_NOT_PLAYED);

            $round[] = $set;
        }

        return $round;
    }

    /**
     * @param int $roundNumber
     * @return string
     */
    protected function getRoundName($roundNumber)
    {
        $roundsBeforeEnd = $this->getRoundsRequired() - $roundNumber;

        switch ($roundsBeforeEnd) {
            case 0:
                return 'Finals';
            case 1:
                return 'Semifinals';
            case 2:
                return 'Quarterfinals';
        }

        return 'Round '.$roundNumber;
    }

    /**
     * @param array $bracket
     * @param int   $roundNumber
     * @param Set[] $sets
     * @return array
     */
    protected function matchSetsForRound($bracket, $roundNumber, $sets)
    {
        /** @var Set $set */
        foreach ($bracket[$roundNumber] as &$set) {
            if (count($sets) > 0) {
                $newSet = array_shift($sets);
                $newSet->setRoundName($set->getRoundName());
                $newSet->setLoserRank($set->getLoserRank());
                $newSet->setIsFinals($set->isFinals());
                $newSet->setIsGrandFinals($set->isGrandFinals());

                $set = $newSet;
                $set->setIsOrphaned(false);
            }
        }

        if (count($sets) > 0) {
            foreach ($sets as $set) {
                $set->setIsOrphaned(true);
            }
        }

        return $bracket;
    }
}
