<?php

declare(strict_types = 1);

namespace App\RestApi\Response;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class KnpPaginatedResponse implements PaginatedResponseInterface
{
    /**
     * @var SlidingPagination
     */
    protected $pagination;

    /**
     * @param $pagination
     */
    public function __construct(SlidingPagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $data = [];

        foreach ($this->pagination as $item) {
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->pagination->getPaginationData();
    }
}
