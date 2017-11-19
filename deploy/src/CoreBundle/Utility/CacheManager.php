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
     */
    public function onPlayerChange(Player $player)
    {
        return;
    }

    /**
     * @param Entrant $entrant
     */
    public function onEntrantChange(Entrant $entrant)
    {
        foreach ($entrant->getPlayers() as $player) {
            $this->onPlayerChange($player);
        }
    }

    /**
     * @param Tournament $tournament
     */
    public function onTournamentChange(Tournament $tournament)
    {
        return;
    }

    /**
     * @param Result[] $results
     */
    public function onResultsChange($results)
    {
        return;
    }
}
