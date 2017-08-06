<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Psr\Cache\CacheItemPoolInterface as Cache;
use Sonata\AdminBundle\Controller\CRUDController as Controller;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AbstractController extends Controller
{
    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param string $name
     * @return ObjectRepository
     */
    public function getRepository(string $name)
    {
        return $this->getEntityManager()->getRepository($name);
    }

    /**
     * @return Cache|TaggableCacheItemPoolInterface
     */
    public function getCache()
    {
        return $this->get('cache.provider.filesystem');
    }

    /**
     * @param object $command
     * @return mixed
     */
    public function handleCommand($command)
    {
        return $this->get('tactician.commandbus')->handle($command);
    }
}
