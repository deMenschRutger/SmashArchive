<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket\RoundRobin;

use CoreBundle\Bracket\AbstractResultsGenerator;
use CoreBundle\Entity\Event;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsGenerator extends AbstractResultsGenerator
{
    /**
     * @var int
     */
    protected $winnerRank = 1;

    /**
     * @param Event $event
     * @return array
     */
    public function getResults(Event $event)
    {
        return [];
    }
}
