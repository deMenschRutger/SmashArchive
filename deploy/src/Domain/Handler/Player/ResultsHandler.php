<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\DataTransferObject\SetDTO;
use CoreBundle\Entity\Set;
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
     */
    public function handle(ResultsCommand $command)
    {
        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');
        $sets = $repository->findByPlayerSlug($command->getSlug());

        return array_map(function (Set $set) {
            return new SetDTO($set);
        }, $sets);
    }
}
