<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DoubleEliminationBracket extends AbstractBracket
{
    /**
     * @var array
     */
    protected $winnersBracketSets;

    /**
     * @var array
     */
    protected $losersBracketSets;

    /**
     * @return void
     */
    protected function init()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $this->addEntrant($set->getEntrantOne());
            $this->addEntrant($set->getEntrantTwo());

            $round = $set->getRound();
            $this->setsByRound[$round][] = $set;
        }

        $this->winnersBracketSets = $this->getWinnersBracketSetsByRound();
        $this->losersBracketSets = $this->getLosersBracketSetsByRound();
    }

    /**
     * @param Set $set
     */
    public function determineRoundName(Set $set)
    {
        $entrantCount = count($this->entrants);
        $totalWinnersRounds = ceil(log($entrantCount, 2));
        $totalLosersRounds = ($totalWinnersRounds - 1) * 2;

        $name = null;
        $round = $set->getRound();

        if ($round > 0) {
            $reverseIndex = $this->getReverseIndex($set);

            switch ($reverseIndex) {
                case 0:
                    $name = 'Winners finals';
                    break;

                case 1:
                    $name = 'Winners semifinals';
                    break;

                case 2:
                    $name = 'Winners quarterfinals';
                    break;

                case ($round > $totalWinnersRounds):
                    $name = 'Grand finals';
                    break;

                default:
                    $name = 'Winners round '.$set->getRound();
                    break;
            }
        } elseif ($round < 0) {
            // TODO Determine the name of the losers round here.
        }

        $set->setRoundName($name);
    }

    /**
     * @param Set $set
     */
    public function determineIsGrandFinals(Set $set)
    {
        // TODO Implement this method.
    }

    /**
     * @return array
     */
    protected function getWinnersBracketSetsByRound()
    {
        $setsByRound = [];

        foreach ($this->setsByRound as $round => $sets) {
            if ($round > 0) {
                $setsByRound[$round] = $sets;
            }
        }

        ksort($setsByRound);

        return $setsByRound;
    }
    /**
     * @return array
     */
    protected function getLosersBracketSetsByRound()
    {
        $setsByRound = [];

        foreach ($this->setsByRound as $round => $sets) {
            if ($round < 0) {
                $setsByRound[$round] = $sets;
            }
        }

        return $setsByRound;
    }
}