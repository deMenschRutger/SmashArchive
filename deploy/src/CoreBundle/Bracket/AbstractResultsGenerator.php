<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractResultsGenerator
{
    /**
     * @var AbstractBracket
     */
    protected $bracket;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var int
     */
    protected $currentRank = 1;


    /**
     * @param AbstractBracket $bracket
     */
    public function __construct(AbstractBracket $bracket)
    {
        $this->bracket = $bracket;
    }

    /**
     * @return array
     */
    abstract public function getResults();

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
     * @param Entrant $entrant
     */
    protected function addResult(Entrant $entrant)
    {
        $this->results[$this->currentRank][] = $entrant;
    }
}
