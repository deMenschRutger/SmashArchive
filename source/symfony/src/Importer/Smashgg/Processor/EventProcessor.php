<?php

declare(strict_types = 1);

namespace App\Importer\Smashgg\Processor;

use App\Entity\Event;
use App\Entity\Game;
use App\Entity\Tournament;
use App\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventProcessor extends AbstractProcessor
{
    /**
     * @var Event[]
     */
    protected $events = [];

    /**
     * @param int $eventId
     *
     * @return bool
     */
    public function hasEvent($eventId)
    {
        return array_key_exists($eventId, $this->events);
    }

    /**
     * @param int $eventId
     *
     * @return Event
     */
    public function findEvent($eventId)
    {
        if ($this->hasEvent($eventId)) {
            return $this->events[$eventId];
        }

        return null;
    }

    /**
     * @return Event[]
     */
    public function getAllEvents()
    {
        return $this->events;
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
        $event = $this->entityManager->getRepository('App:Event')->findOneBy([
            'externalId' => $eventId,
        ]);

        if (!$event instanceof Event) {
            // The event does not exist in the database yet, so create it.
            $event = new Event();
            $event->setExternalId(strval($eventId));

            $this->entityManager->persist($event);
        }

        $event->setTournament($tournament);
        $event->setName($eventData['name']);
        $event->setGame($game);

        $this->events[$eventId] = $event;
    }

    /**
     * This will remove existing entities that were not imported from the database.
     *
     * @param Tournament $tournament
     */
    public function cleanUp(Tournament $tournament)
    {
        $events = $this->entityManager->getRepository('App:Event')->findBy([
            'tournament' => $tournament,
        ]);

        foreach ($events as $event) {
            $eventId = $event->getExternalId();

            if ($this->hasEvent($eventId)) {
                continue;
            }

            $this->entityManager->remove($event);
        }
    }
}
