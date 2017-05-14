<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\SingleElimination\ResultsGenerator as SingleEliminationResultsGenerator;
use CoreBundle\Entity\Entrant;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends SingleEliminationResultsGenerator
{
    /**
     * @var Bracket
     */
    protected $bracket;

    /**
     * @return array
     */
    public function getResults()
    {
        $grandFinals = $this->bracket->getGrandFinalsSet();

        if ($grandFinals->getWinner() instanceof Entrant) {
            $this->addResult($grandFinals->getWinner());
            $this->moveRound();
            $this->addResult($grandFinals->getLoser());
        } else {
            $this->addResult($grandFinals->getEntrantOne());
            $this->addResult($grandFinals->getEntrantTwo());
        }

        $rounds = array_reverse($this->bracket->getLosersBracketRounds());

        $this->processNextRound($rounds);

        return array_filter($this->results);
    }
}
