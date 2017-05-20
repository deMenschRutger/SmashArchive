<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\SingleElimination;

use CoreBundle\Bracket\AbstractBracket;
use CoreBundle\Entity\Entrant;
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
        $bracket = $this->generateVirtualBracket();
        $roundsRequired = $this->getRoundsRequired();

        foreach ($this->getRounds() as $index => $roundNumber) {
            $sets = $this->getSetsForRound($roundNumber);
            $bracket = $this->matchSetsForRound($bracket, $index + 1, $sets);

            if ($index + 1 >= $roundsRequired) {
                break;
            }
        }

        return $bracket;
    }

    /**
     * @return array
     */
    protected function generateVirtualBracket()
    {
        $rounds = $this->getRoundsRequired();
        $bracket = [];

        for ($round = 1; $round <= $rounds; $round++) {
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

        for ($i = 1; $i <= $setCount; $i++) {
            $round[] = [
                'number' => $i,
                'roundName' => $this->getRoundName($roundNumber),
                'loserRank' => $setCount + 1,
                'isFinals' => $setCount == 1,
            ];
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
        foreach ($bracket[$roundNumber] as &$set) {
            if (count($sets) > 0) {
                $newSet = array_shift($sets);
            } else {
                $newSet = array_shift($sets);
            }

            $newSet->setRoundName($set['roundName']);
            $newSet->setLoserRank($set['loserRank']);
            $newSet->setIsGrandFinals($set['isFinals']);

            $set = $newSet;
        }

        if (count($sets) > 0) {
            // TODO Mark sets that weren't processed as orphans.
            assert(count($sets) === 0);
        }

        return $bracket;
    }












    /**
     * @return void
     */
    protected function generateBracket()
    {
        $rounds = $this->getRounds();
        $round = array_pop($rounds);

        $sets = $this->getSetsForRound($round);
        $orderedSets = [];

        /** @var Set $set */
        foreach ($sets as $index => $set) {
            $orderedSets[$index + 1] = $set;
        }

        $this->setsByRound[$round] = $orderedSets;

        $nextOrder = [];
        $position = 1;

        /** @var Set $set */
        foreach ($sets as $set) {
            $nextOrder[$position] = $set->getEntrantOne();
            $position++;

            $nextOrder[$position] = $set->getEntrantTwo();
            $position++;
        }

        $this->generateRound($rounds, $nextOrder);
    }

    /**
     * @param array $rounds
     * @param array $order
     * @return void
     */
    protected function generateRound($rounds, $order)
    {
        if (count($rounds) === 0) {
            return;
        }

        $round = array_pop($rounds);
        $sets = $this->getSetsForRound($round);

        /** @var Entrant $entrant */
        foreach ($order as $position => $entrant) {
            $set = array_reduce($sets, function ($carry, $item) use ($entrant) {
                if ($carry instanceof Set) {
                    return $carry;
                }

                if ($item instanceof Set && $item->hasEntrant($entrant)) {
                    return $item;
                }

                return null;
            });

            if (!$set instanceof Set) {
                // TODO Fake set required?
                //unset($order[$position]);
            } else {
                $order[$position] = $set;
            }
        }

        $this->setsByRound[$round] = $order;

        $nextOrder = [];
        $position = 1;

        /** @var Set $set */
        foreach ($order as $index => $set) {
            $nextOrder[$position] = $set->getEntrantOne();
            $position++;

            $nextOrder[$position] = $set->getEntrantTwo();
            $position++;
        }

        $this->generateRound($rounds, $nextOrder);
    }
}
