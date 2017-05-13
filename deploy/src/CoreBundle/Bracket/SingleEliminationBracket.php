<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SingleEliminationBracket extends AbstractBracket
{
    /**
     * @var array
     */
    protected $roundMapping;

    /**
     * @return void
     */
    public function orderAllRounds()
    {
        $rounds = $this->rounds;
        $round = array_pop($rounds);

        $sets = $this->getSetsByRound($round);
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

        $this->orderRound($rounds, $nextOrder);
    }

    /**
     * @param array $rounds
     * @param array $order
     * @return void
     */
    public function orderRound($rounds, $order)
    {
        if (count($rounds) === 0) {
            return;
        }

        $round = array_pop($rounds);
        $sets = $this->getSetsByRound($round);

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
                // TODO Fake set required.
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

        $this->orderRound($rounds, $nextOrder);
    }

    /**
     * A single elimination bracket can never have grand finals.
     *
     * @param Set $set
     * @return void
     */
    public function determineIsGrandFinals(Set $set)
    {
        return;
    }

    /**
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->roundMapping = $this->getRoundMapping();
        $lastRound = max(array_values($this->roundMapping));

        foreach ($this->roundMapping as $round => &$mappedRound) {
            $name = 'Round '.$mappedRound;

            switch ($lastRound - $mappedRound) {
                case 0:
                    $name = 'Finals';
                    break;
                case 1:
                    $name = 'Semifinals';
                    break;
                case 2:
                    $name = 'Quarterfinals';
                    break;
            }

            $mappedRound = [
                'mappedRound'   => $mappedRound,
                'name'          => $name,
                'isGrandFinals' => false,
            ];
        }
    }

    /**
     * @return array
     */
    protected function getRoundMapping()
    {
        $mapping = [];

        foreach ($this->rounds as $round) {
            $mapping[$round] = $round;
        }

        ksort($mapping, SORT_STRING);
        $counter = 1;

        foreach ($mapping as &$round) {
            $round = $counter;
            $counter += 1;
        }

        return $mapping;
    }

    /**
     * @param Set $set
     * @return int
     */
    protected function getMappedRound(Set $set)
    {
        $round = $set->getRound();

        if (array_key_exists($round, $this->roundMapping)) {
            return $this->roundMapping[$round];
        }

        return null;
    }
}
