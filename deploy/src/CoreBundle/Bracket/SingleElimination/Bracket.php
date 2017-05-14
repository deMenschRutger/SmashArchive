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
                unset($order[$position]);
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
