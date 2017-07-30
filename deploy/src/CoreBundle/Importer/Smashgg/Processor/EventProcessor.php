<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Tournament;
use CoreBundle\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManager;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventProcessor
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @var Event[]
     */
    protected $events = [];

    /**
     * @param EntityManager $entityManager
     * @param Smashgg       $smashgg
     */
    public function __construct(EntityManager $entityManager, Smashgg $smashgg)
    {
        $this->entityManager = $entityManager;
        $this->smashgg = $smashgg;
    }

    /**
     * @param int $eventId
     * @return bool
     */
    public function hasEvent($eventId)
    {
        return array_key_exists($eventId, $this->events);
    }

    /**
     * @param int $eventId
     * @return Event
     */
    public function getEvent($eventId)
    {
        if ($this->hasEvent($eventId)) {
            return $this->events[$eventId];
        }

        return null;
    }

    /**
     * @param array      $eventData
     * @param Tournament $tournament
     * @param Game       $game
     */
    public function processNew(array $eventData, Tournament $tournament, Game $game)
    {
        // The event ID on smash.gg.
        $eventId = $eventData['id'];

        if ($this->hasEvent($eventId)) {
            // An event with this ID was found before (within this import process), so no additional processing is necessary.
            return;
        }

        // Try to find an existing event in the database.
        $event = $this->entityManager->getRepository('CoreBundle:Event')->findOneBy([
            'smashggId' => $eventId,
        ]);

        if (!$event instanceof Event) {
            // The event does not exist in the database yet, so create it.
            $event = new Event();
            $event->setSmashggId($eventId);

            $this->entityManager->persist($event);
        }

        $event->setTournament($tournament);
        $event->setName($eventData['name']);
        $event->setDescription($eventData['description']);
        $event->setGame($game);

        $this->events[$eventId] = $event;
    }

    /**
     *
     * This will remove existing entities that were not imported from the database.
     *
     * @param Tournament $tournament
     */
    public function cleanUp(Tournament $tournament)
    {
        $events = $this->entityManager->getRepository('CoreBundle:Event')->findBy([
            'tournament' => $tournament,
        ]);

        foreach ($events as $event) {
            $eventId = $event->getSmashggId();

            if ($this->hasEvent($eventId)) {
                continue;
            }

            $this->entityManager->remove($event);
        }
    }
}
