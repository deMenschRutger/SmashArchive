<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Entrant;
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
     */
    public function handle(ResultsCommand $command)
    {
        $slug = $command->getProfileSlug();
        $eventId = $command->getEventId();

        /** @var EntrantRepository $entrantRepository */
        $entrantRepository = $this->getRepository('CoreBundle:Entrant');
        $entrants = $entrantRepository->findByProfileSlug($slug, $eventId);

        /** @var ResultRepository $resultRepository */
        $resultRepository = $this->getRepository('CoreBundle:Result');
        $ranks = $resultRepository->findForProfile($slug, $eventId);

        $results = [];

        foreach ($entrants as $entrant) {
            $result = new Result();
            $result->setEntrant($entrant);

            $event = $entrant->getOriginEvent();

            if ($event instanceof Event) {
                $rank = $this->findRank($entrant, $event, $ranks);

                $result->setEvent($event);
                $result->setRank($rank);
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * @param Entrant  $entrant
     * @param Event    $event
     * @param Result[] $results
     * @return int|null
     */
    protected function findRank(Entrant $entrant, Event $event, array $results)
    {
        foreach ($results as $result) {
            if ($result->getEntrant() === $entrant && $result->getEvent() === $event) {
                return $result->getRank();
            }
        }

        return null;
    }
}
