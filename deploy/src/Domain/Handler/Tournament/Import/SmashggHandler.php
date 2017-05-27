<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament\Import;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Tournament;
use Domain\Command\Tournament\Import\SmashggCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashggHandler extends AbstractHandler
{
    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @param SmashggCommand $command
     */
    public function handle(SmashggCommand $command)
    {
        $this->tournament = $this->getTournament($command->getSlug());

        $this->handleExistingEvents($command->getEventIds(), $command->getForce());
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    protected function getTournament($slug)
    {
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'smashggSlug' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSmashggSlug($slug);

            $this->entityManager->persist($tournament);
        }

        return $tournament;
    }

    /**
     * @param array $eventIds
     * @param bool  $force
     */
    protected function handleExistingEvents(array $eventIds, bool $force = false)
    {
        $events = $this->getRepository('CoreBundle:Event')->findBy([
            'smashggId' => $eventIds,
        ]);

        if (count($events) > 0 && !$force) {
            $names = [];

            /** @var Event $event */
            foreach ($events as $event) {
                $names[] = $event->getName();
            }

            $message = join(' ', [
                'The following events already exist in the database: %s. Please add the force flag (-f) if you wish to override these',
                'events with the most recent event data from smash.gg. Please note that this will remove all existing data for the event,',
                'even the data that was modified after the event was originally imported.',
            ]);

            throw new \InvalidArgumentException(sprintf($message, join(', ', $names)));
        }
    }
}
