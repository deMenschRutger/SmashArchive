<?php

declare(strict_types = 1);

namespace CoreBundle\Utility;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use CoreBundle\Entity\Player;
use Psr\Cache\CacheItemPoolInterface as Cache;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class CacheManager
{
    /**
     * @var Cache|TaggableCacheItemPoolInterface
     */
    protected $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Player $player
     */
    public function onPlayerProfileUpdate(Player $player)
    {
        $this->cache->invalidateTag($player->getCacheTag());
    }
}
