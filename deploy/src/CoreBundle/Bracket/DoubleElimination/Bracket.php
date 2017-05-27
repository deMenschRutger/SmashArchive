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
     * @return Set[]
     */
    public function getGrandFinalsSets()
    {
        $round = key($this->grandFinals);
        $sets = [];

        for ($i = 0; $i <= 1; $i++) {
            $set = new Set();
            $set->setRoundName('Grand Finals');
            $set->setLoserRank(2);
            $set->setIsFinals(true);

            $sets[$round][] = $set;
        }

        $setsForRound = $this->grandFinals[$round];

        return $this->matchSetsForRound($sets, $round, $setsForRound)[$round];
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
            $bracket[$roundNumber] = $this->generateVirtualLosersRound($round, $round == $roundsRequired);
        }

        return $bracket;
    }

    /**
     * @param int  $initialRoundNumber
     * @param bool $isFinals
     * @return array
     */
    protected function generateVirtualLosersRound($initialRoundNumber, $isFinals)
    {
        $roundIsOdd = $initialRoundNumber % 2 !== 0;
        $bracketSize = $this->getBracketSize() / 2;
        $roundNumber = ceil($initialRoundNumber / 2);
        $setCount = $bracketSize / pow(2, $roundNumber);
        $round = [];

        $loserRank = $setCount * 2 + 1;

        if ($roundIsOdd) {
            $loserRank += $setCount;
        }

        for ($i = 1; $i <= $setCount; $i++) {
            $set = new Set();
            $set->setRoundName($this->getLoserRoundName($initialRoundNumber));
            $set->setLoserRank($loserRank);
            $set->setIsFinals($isFinals);

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
                return 'Winners Finals';
            case 1:
                return 'Winners Semifinals';
            case 2:
                return 'Winners Quarterfinals';
        }

        return 'Winners Round '.$roundNumber;
    }

    /**
     * @param int $roundNumber
     * @return string
     */
    protected function getLoserRoundName($roundNumber)
    {
        $roundsRequired = ($this->getRoundsRequired() * 2) - 2;
        $roundsBeforeEnd = $roundsRequired - $roundNumber;

        switch ($roundsBeforeEnd) {
            case 0:
                return 'Losers Finals';
            case 1:
                return 'Losers Semifinals';
            case 2:
                return 'Losers Quarterfinals';
        }

        return 'Losers Round '.$roundNumber;
    }

    /**
     * @return void
     */
    protected function processSets()
    {
        parent::processSets();

        end($this->setsByRound);
        $round = key($this->setsByRound);
        $this->grandFinals[$round] = array_pop($this->setsByRound);

        /** @var Set $set */
        foreach ($this->grandFinals[$round] as $set) {
            $set->setIsGrandFinals(true);
        }

        foreach ($this->setsByRound as $round => $sets) {
            if ($round < 0) {
                $this->losersBracketSetsByRound[$round] = $sets;
            } else {
                $this->winnersBracketSetsByRound[$round] = $sets;
            }
        }
    }
}
