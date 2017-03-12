<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\Repository\SetRepository;
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
     *
     * @TODO This information probably needs to be paginated.
     */
    public function handle(ResultsCommand $command)
    {
        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');

        return $repository->findByPlayerSlug($command->getSlug());
    }
}
