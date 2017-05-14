<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\AbstractBracket;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Bracket extends AbstractBracket
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
    public function getGrandFinalsRounds()
    {
        return array_keys($this->grandFinals);
    }

    /**
     * @return Set
     */
    public function getGrandFinalsSet()
    {
        $lastRound = end($this->grandFinals);

        return end($lastRound);
    }

    /**
     * @return void
     */
    protected function processSets()
    {
        parent::processSets();

        foreach ($this->setsByRound as $round => $sets) {
            if ($round < 0) {
                $this->losersBracketSetsByRound[$round] = $sets;
            } else {
                /** @var Set $firstSet */
                $firstSet = current($sets);

                if ($firstSet->getIsGrandFinals()) {
                    // If the first set is the grand finals, it is assumed that all sets in this round are grand finals.
                    $this->grandFinals[$round] = $sets;
                } else {
                    $this->winnersBracketSetsByRound[$round] = $sets;
                }
            }
        }
    }

    /**
     * @return void
     *
     * @TODO Implement this method.
     */
    protected function generateBracket()
    {
    }
}
