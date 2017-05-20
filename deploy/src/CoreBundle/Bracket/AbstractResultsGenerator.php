<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Result;

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
     * @param AbstractBracket $bracket
     */
    public function __construct(AbstractBracket $bracket)
    {
        $this->bracket = $bracket;
    }

    /**
     * @param Event $event
     * @return array
     */
    abstract public function getResults(Event $event);

    /**
     * @param Event   $event
     * @param Entrant $entrant
     * @param int     $rank
     */
    protected function addResult(Event $event, Entrant $entrant, $rank)
    {
        $result = new Result();
        $result->setEvent($event);
        $result->setEntrant($entrant);
        $result->setRank($rank);

        $this->results[$entrant->getId()] = $result;
    }

    /**
     * @return void
     */
    protected function sortResults()
    {
        usort($this->results, function (Result $a, Result $b) {
            if ($a->getRank() === $b->getRank()) {
                return 0;
            }

            return $a->getRank() > $b->getRank();
        });
    }
}
