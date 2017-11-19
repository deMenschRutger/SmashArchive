<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

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
        /** @var ResultRepository $repository */
        $repository = $this->getRepository('CoreBundle:Result');

        $slug = $command->getPlayerSlug();
        $eventId = $command->getEventId();

        if ($eventId) {
            return $repository->findByPlayerSlugAndEventId($slug, $eventId);
        } else {
            return $repository->findByPlayerSlug($slug);
        }
    }
}
