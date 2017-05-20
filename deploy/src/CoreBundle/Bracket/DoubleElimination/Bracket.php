<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Bracket extends SingleEliminationBracket
{
    /**
     * @var array
     */
    protected $winnersBracketSetsByRound = [];

    /**
     * @var array
     */
    protected $losersBracketSetsByRound = [];

    /**
     * @var array
     */
    protected $grandFinals = [];

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
    public function getWinnersBracketRounds()
    {
        return array_keys($this->winnersBracketSetsByRound);
    }

    /**
     * @return array
     */
    public function getWinnersBracketSetsByRound()
    {
        return $this->winnersBracketSetsByRound;
    }

    /**
     * @return array
     */
    public function getLosersBracketRounds()
    {
        return array_keys($this->losersBracketSetsByRound);
    }

    /**
     * @return array
     */
    public function getLosersBracketSetsByRound()
    {
        return $this->losersBracketSetsByRound;
    }

    /**
     * @return Set[]
     */
    public function getGrandFinalsSets()
    {
        return $this->grandFinals;
    }

    /**
     * @return array
     */
    public function getIterableLosersBracket()
    {
        $bracket = $this->generateVirtualLosersBracket();
        $roundsRequired = ($this->getRoundsRequired() * 2) - 2;

        foreach ($this->getLosersBracketRounds() as $index => $roundNumber) {
            $sets = $this->getSetsForRound($roundNumber);
            $bracket = $this->matchSetsForRound($bracket, 0 - $roundsRequired + $index, $sets);

            if ($index + 1 >= $roundsRequired) {
                break;
            }
        }

        return $bracket;
    }

    /**
     * @return array
     */
    protected function generateVirtualLosersBracket()
    {
        $roundsRequired = ($this->getRoundsRequired() * 2) - 2;
        $bracket = [];

        for ($round = 1; $round <= $roundsRequired; $round++) {
            $roundNumber = 0 - $round;
            $bracket[$roundNumber] = $this->generateVirtualLosersRound($round);
        }

        return $bracket;
    }

    /**
     * @param int $roundNumber
     * @return array
     */
    protected function generateVirtualLosersRound($roundNumber)
    {
        $bracketSize = $this->getBracketSize() / 2;
        $roundNumber = ceil($roundNumber / 2);
        $setCount = $bracketSize / pow(2, $roundNumber);
        $round = [];

        for ($i = 1; $i <= $setCount; $i++) {
            $set = new Set();
            $set->setRoundName(''); // TODO Determine the round name.
            $set->setLoserRank(1); // TODO Determine the rank of the loser.
            $set->setIsGrandFinals(false); // TODO Determine if this set is a final.

            $round[] = $set;
        }

        return $round;
    }

    /**
     * @return void
     */
    protected function processSets()
    {
        parent::processSets();

        $this->grandFinals = array_pop($this->setsByRound);

        foreach ($this->setsByRound as $round => $sets) {
            if ($round < 0) {
                $this->losersBracketSetsByRound[$round] = $sets;
            } else {
                $this->winnersBracketSetsByRound[$round] = $sets;
            }
        }
    }
}
