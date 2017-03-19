<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

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