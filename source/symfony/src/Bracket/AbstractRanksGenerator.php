<?php

declare(strict_types = 1);

namespace App\Bracket;

use App\Entity\Entrant;
use App\Entity\Event;
use App\Entity\Rank;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractRanksGenerator
{
    /**
     * @var AbstractBracket
     */
    protected $bracket;

    /**
     * @var array
     */
    protected $ranks = [];

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
    abstract public function getRanks(Event $event);

    /**
     * @param Event   $event
     * @param Entrant $entrant
     * @param int     $rank
     */
    protected function addRank(Event $event, Entrant $entrant, $rank)
    {
        $entity = new Rank();
        $entity->setEvent($event);
        $entity->setEntrant($entrant);
        $entity->setRank($rank);

        $this->ranks[$entrant->getId()] = $entity;
    }

    /**
     * @return void
     */
    protected function sortRanks()
    {
        usort($this->ranks, function (Rank $a, Rank $b) {
            if ($a->getRank() === $b->getRank()) {
                return 0;
            }

            return $a->getRank() > $b->getRank();
        });
    }
}
