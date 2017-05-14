<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\SingleElimination;

use CoreBundle\Bracket\AbstractResultsGenerator;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends AbstractResultsGenerator
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var int
     */
    protected $currentRank = 1;

    /**
     * @return array
     */
    public function getResults()
    {
        $rounds = $this->bracket->getRounds();
        $round = max($rounds);
        $sets = $this->bracket->getSetsForRound($round);

        if (count($sets) === 0) {
            return [];
        }

        $this->moveRound();

        /** @var Set $set */
        foreach ($sets as $set) {
            $winner = $set->getWinner();

            if (!$winner instanceof Entrant) {
                continue;
            }

            $this->addResult($winner);
        }

        $this->processNextRound($rounds);

        return $this->results;
    }

    /**
     * @return void
     */
    protected function moveRound()
    {
        if (count($this->results) === 0) {
            $this->results[1] = [];
        } else {
            $this->currentRank += count($this->results[$this->currentRank]);
            $this->results[$this->currentRank] = [];
        }
    }

    /**
     * @param array $rounds
     */
    protected function processNextRound(array $rounds)
    {
        $round = array_pop($rounds);

        if ($round === null) {
            return;
        }

        $sets = $this->bracket->getSetsForRound($round);

        if (count($sets) === 0) {
            return;
        }

        $this->moveRound();

        /** @var Set $set */
        foreach ($sets as $set) {
            $loser = $set->getLoser();

            if (!$loser instanceof Entrant) {
                continue;
            }

            $this->addResult($loser);
        }

        $this->processNextRound($rounds);
    }

    /**
     * @param Entrant $entrant
     */
    protected function addResult(Entrant $entrant)
    {
        $this->results[$this->currentRank][] = $entrant;
    }
}
