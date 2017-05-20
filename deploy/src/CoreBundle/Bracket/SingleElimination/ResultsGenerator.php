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
     * @return array
     */
    public function getResults()
    {







        var_dump($this->bracket->generateVirtualBracket());
        die;




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

        return array_filter($this->results);
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
}
