<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\DoubleElimination;

use CoreBundle\Bracket\AbstractRoundMapper;
use CoreBundle\Entity\Set;
use Webmozart\Assert\Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RoundMapper extends AbstractRoundMapper
{
    /**
     * @var array
     */
    protected $winnersBracketRoundMapping;

    /**
     * @var array
     */
    protected $losersBracketRoundMapping;

    /**
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * @return void
     */
    protected function load()
    {
        $this->determineWinnersBracketRoundMapping();
        $this->determineLosersBracketRoundMapping();

        $this->isLoaded = true;
    }

    /**
     * @return void
     */
    protected function determineWinnersBracketRoundMapping()
    {
        $this->winnersBracketRoundMapping = $this->getRoundMapping('winners');
        $lastRound = max(array_values($this->winnersBracketRoundMapping));

        foreach ($this->winnersBracketRoundMapping as $round => &$mappedRound) {
            $name = 'Winners round '.$mappedRound;
            $isGrandFinals = false;

            switch ($lastRound - $mappedRound) {
                case 0:
                    $name = 'Grand finals';
                    $isGrandFinals = true;
                    break;

                case 1:
                    $name = 'Winners finals';
                    break;

                case 2:
                    $name = 'Winners semifinals';
                    break;

                case 3:
                    $name = 'Winners quarterfinals';
                    break;
            }

            $mappedRound = [
                'mappedRound'   => $mappedRound,
                'name'          => $name,
                'isGrandFinals' => $isGrandFinals,
            ];
        }
    }
    /**
     * @return void
     */
    protected function determineLosersBracketRoundMapping()
    {
        $this->losersBracketRoundMapping = $this->getRoundMapping('losers');

        if (count($this->losersBracketRoundMapping) === 0) {
            // This can happen for example if the tournament hasn't started yet.
            return;
        }

        $lastRound = max(array_values($this->losersBracketRoundMapping));

        foreach ($this->losersBracketRoundMapping as $round => &$mappedRound) {
            $name = 'Losers round '.$mappedRound;

            switch ($lastRound - $mappedRound) {
                case 0:
                    $name = 'Losers finals';
                    break;

                case 1:
                    $name = 'Losers semifinals';
                    break;

                case 2:
                    $name = 'Losers quarterfinals';
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
     * @param string $bracketPart
     * @return array
     */
    protected function getRoundMapping($bracketPart)
    {
        Assert::oneOf($bracketPart, ['winners', 'losers']);

        $mapping = [];

        foreach ($this->bracket->getRounds() as $round) {
            $addRound = false;

            if ($bracketPart === 'winners') {
                $addRound = $round > 0;
            } elseif ($bracketPart === 'losers') {
                $addRound = $round < 0;
            }

            if ($addRound) {
                $mapping[$round] = $round;
            }
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
        if (!$this->isLoaded) {
            $this->load();
        }

        $round = $set->getRound();
        $mapping = $this->winnersBracketRoundMapping;

        if ($round < 0) {
            $mapping = $this->losersBracketRoundMapping;
        }

        if (array_key_exists($round, $mapping)) {
            return $mapping[$round];
        }

        return null;
    }
}
