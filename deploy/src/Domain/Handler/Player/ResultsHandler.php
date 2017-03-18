<?php

declare(strict_types=1);

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
     * @param ResultsCommand $command
     * @return array
     */
    public function handle(ResultsCommand $command)
    {
        /** @var ResultRepository $repository */
        $repository = $this->getRepository('CoreBundle:Result');

        return $repository->findByPlayerSlug($command->getSlug());
    }
}
