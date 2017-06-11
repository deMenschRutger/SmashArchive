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
        $slug = $command->getSlug();
        $cacheKey = 'player_sets_'.$slug;

        if ($this->isCached($cacheKey)) {
            return $this->getFromCache($cacheKey);
        }

        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');
        $sets = $repository->findByPlayerSlug($slug);

        $tag = 'player_'.$slug;
        $this->saveToCache($cacheKey, $sets, [ $tag ]);

        return $sets;
    }
}
