<?php

declare(strict_types = 1);

namespace Domain\Handler;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use CoreBundle\Utility\CacheManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Cache\CacheItemPoolInterface as Cache;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractHandler
{
    /**
     * @var Cache|TaggableCacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isCached(string $key)
    {
        if ($this->cache instanceof Cache) {
            return $this->cache->hasItem($key);
        }

        return false;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFromCache(string $key)
    {
        if ($this->cache instanceof Cache) {
            return $this->cache->getItem($key)->get();
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $tags
     * @return mixed
     */
    public function saveToCache(string $key, $value, array $tags = [])
    {
        if ($this->cache instanceof Cache) {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $item->setTags($tags);

            $this->cache->save($item);
        }

        return null;
    }

    /**
     * @return CacheManager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     * @return EntityRepository
     */
    public function getRepository(string $name)
    {
        return $this->entityManager->getRepository($name);
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle $io
     */
    public function setIo($io)
    {
        $this->io = $io;
    }
}
