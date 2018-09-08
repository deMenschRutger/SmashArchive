<?php

declare(strict_types = 1);

namespace CoreBundle\Controller;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\Serializer\SerializationContext;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use League\Tactician\CommandBus;
use MediaMonks\RestApiBundle\Response\OffsetPaginatedResponse;
use Psr\Cache\CacheItemPoolInterface as Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractDefaultController extends Controller
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Cache|TaggableCacheItemPoolInterface
     */
    protected $cache;

    /**
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus($commandBus)
    {
        $this->commandBus = $commandBus;
    }

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
     * @param string $name
     * @return ObjectRepository
     */
    public function getRepository(string $name)
    {
        return $this->getDoctrine()->getManager()->getRepository($name);
    }

    /**
     * @param mixed        $data
     * @param array|string $groups
     * @return mixed
     *
     * @TODO The groups serialization strategy needs to be integrated with the REST API bundle.
     */
    protected function serialize($data, $groups)
    {
        $serializer = $this->get('jms_serializer');

        if (!$groups) {
            return $serializer->serialize($data, 'json');
        }

        return $serializer->serialize($data, 'json', SerializationContext::create()->setGroups($groups));
    }

    /**
     * @param mixed        $data
     * @param array|string $groups
     * @return mixed
     */
    protected function buildResponse($data, $groups)
    {
        $data = $this->serialize($data, $groups);

        return \GuzzleHttp\json_decode($data);
    }

    /**
     * @param SlidingPagination $pagination
     * @param array|string      $groups
     * @return OffsetPaginatedResponse
     */
    protected function buildPaginatedResponse(SlidingPagination $pagination, $groups)
    {
        $data = [];

        foreach ($pagination as $item) {
            $data[] = $item;
        }

        $data = $this->serialize($data, $groups);

        $paginationData = $pagination->getPaginationData();
        $offset = $paginationData['firstItemNumber'] - 1;
        $limit = $paginationData['numItemsPerPage'];
        $total = $paginationData['totalCount'];

        return new OffsetPaginatedResponse(\GuzzleHttp\json_decode($data), $offset, $limit, $total);
    }
}
