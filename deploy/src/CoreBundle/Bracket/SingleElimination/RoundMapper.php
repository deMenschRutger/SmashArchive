<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\SingleElimination;

use CoreBundle\Bracket\AbstractRoundMapper;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RoundMapper extends AbstractRoundMapper
{
    /**
     * @var array
     */
    protected $mapping;

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
    protected function load()
    {
        $this->mapping = $this->getMapping();
        $lastRound = max(array_values($this->mapping));

        foreach ($this->mapping as $round => &$mappedRound) {
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
    protected function getMapping()
    {
        $mapping = [];

        foreach ($this->bracket->getRounds() as $round) {
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
        if (!$this->mapping) {
            $this->load();
        }

        $round = $set->getRound();

        if (array_key_exists($round, $this->mapping)) {
            return $this->mapping[$round];
        }

        return null;
    }
}
