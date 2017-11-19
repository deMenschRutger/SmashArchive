<?php

declare(strict_types = 1);

namespace CoreBundle\Utility;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Result;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param Cache                  $cache
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Cache $cache, EntityManagerInterface $entityManager)
    {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Player $player
     * @param bool   $clearResults
     * @param bool   $clearOpponentResults
     */
    public function onPlayerChange(Player $player, bool $clearResults = false, bool $clearOpponentResults = false)
    {
        if ($clearOpponentResults) {
            $playerRepository = $this->entityManager->getRepository('CoreBundle:Player');
            $opponents = $playerRepository->findOpponents($player->getSlug());

            foreach ($opponents as $opponent) {
                $this->onPlayerChange($opponent, true, false);
            }
        }

        if ($clearResults) {
            $this->cache->invalidateTag($player->getResultsCacheTag());
        }

        $this->cache->invalidateTag($player->getCacheTag());
    }

    /**
     * @param Entrant $entrant
     */
    public function onEntrantChange(Entrant $entrant)
    {
        foreach ($entrant->getPlayers() as $player) {
            $this->onPlayerChange($player, true, true);
        }
    }

    /**
     * @param Tournament $tournament
     */
    public function onTournamentChange(Tournament $tournament)
    {
        /** @var Player $player */
        foreach ($tournament->getPlayers() as $player) {
            $this->cache->invalidateTag($player->getCacheTag());
            $this->cache->invalidateTag($player->getResultsCacheTag());
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
            foreach ($result->getPlayers() as $player) {
                $this->cache->invalidateTag($player->getCacheTag());
            }
        }
    }
}
