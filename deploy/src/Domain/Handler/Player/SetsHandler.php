<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Repository\SetRepository;
use Domain\Command\Player\SetsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsHandler extends AbstractHandler
{
    /**
     * @param SetsCommand $command
     * @return array
     *
     * @TODO This information probably needs to be paginated somehow.
     */
    public function handle(SetsCommand $command)
    {
        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');

         return $repository->findByPlayerSlug($command->getSlug());
    }
}
