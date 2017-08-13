<?php

declare(strict_types = 1);

namespace CoreBundle\Utility;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Result;
use CoreBundle\Entity\Tournament;
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
     *
     * @TODO When changing a player's slug or sets played, or when the player is removed, clear the cache of all his opponents as well.
     * @TODO When updating a player's sets, also clear the cache for the tag 'player_results_{slug}'.
     */
    public function onPlayerChange(Player $player)
    {
        $this->cache->invalidateTag($player->getCacheTag());
    }

    /**
     * @param Tournament $tournament
     */
    public function onTournamentChange(Tournament $tournament)
    {
        /** @var Player $player */
        foreach ($tournament->getPlayers() as $player) {
            $this->cache->invalidateTag($player->getCacheTag());
        }
    }

    /**
     * @param Result[] $results
     *
     * @TODO Can the player retrieval and cache clear be optimized?
     */
    public function onResultsChange($results)
    {
        foreach ($results as $result) {
            /** @var Player $player */
            foreach ($result->getPlayers() as $player) {
                $this->cache->invalidateTag($player->getCacheTag());
            }
        }
    }
}
