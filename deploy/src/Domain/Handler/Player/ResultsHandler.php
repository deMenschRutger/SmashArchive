<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Result;
use CoreBundle\Repository\EntrantRepository;
use CoreBundle\Repository\ResultRepository;
use Domain\Command\Player\ResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsHandler extends AbstractHandler
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $setsByEventId = [];

    /**
     * @param ResultsCommand $command
     * @return array
     *
     * @TODO Combine this with generated rankings for the events.
     */
    public function handle(ResultsCommand $command)
    {
        /** @var EntrantRepository $entrantRepository */
        $entrantRepository = $this->getRepository('CoreBundle:Entrant');
        $entrants = $entrantRepository->findByPlayerSlug($command->getPlayerSlug(), $command->getEventId());
        $results = [];

        foreach ($entrants as $entrant) {
            $result = new Result();
            $result->setEntrant($entrant);

            $event = $entrant->getOriginEvent();

            if ($event instanceof Event) {
                $result->setEvent($event);
            }

            $results[] = $result;
        }

        return $results;

        /** @var ResultRepository $repository */
        /*$repository = $this->getRepository('CoreBundle:Result');

        $slug = $command->getPlayerSlug();
        $eventId = $command->getEventId();

        if ($eventId) {
            return $repository->findByPlayerSlugAndEventId($slug, $eventId);
        } else {
            return $repository->findByPlayerSlug($slug);
        }*/
    }
}
