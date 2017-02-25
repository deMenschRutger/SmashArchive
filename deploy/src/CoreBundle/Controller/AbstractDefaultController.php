<?php

declare(strict_types=1);

namespace CoreBundle\Controller;

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
     * @param array             $data
     * @param SlidingPagination $pagination
     * @return OffsetPaginatedResponse
     */
    protected function buildPaginatedResponse($data, SlidingPagination $pagination)
    {
        $paginationData = $pagination->getPaginationData();
        $offset = $paginationData['firstItemNumber'] - 1;
        $limit = $paginationData['numItemsPerPage'];
        $total = $paginationData['totalCount'];

        return new OffsetPaginatedResponse($data, $offset, $limit, $total);
    }
}
