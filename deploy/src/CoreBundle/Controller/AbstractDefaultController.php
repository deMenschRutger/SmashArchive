<?php

declare(strict_types=1);

namespace CoreBundle\Controller;

use JMS\Serializer\SerializationContext;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use League\Tactician\CommandBus;
use MediaMonks\RestApiBundle\Response\OffsetPaginatedResponse;
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
