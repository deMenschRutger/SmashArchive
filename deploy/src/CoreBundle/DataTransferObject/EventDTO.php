<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Event;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventDTO
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var TournamentDTO
     */
    public $tournament;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->id = $event->getId();
        $this->name = $event->getName();
        $this->tournament = new TournamentDTO($event->getTournament());
    }
}
