<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AbstractController extends Controller
{
    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param string $entityName
     *
     * @return ObjectRepository
     */
    protected function getRepository($entityName): ObjectRepository
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        /** @var JWTUser $jwtUser */
        $jwtUser = $this->get('security.token_storage')->getToken()->getUser();

        return $this->getRepository('App:User')->find($jwtUser->getUsername());
    }

    /**
     * @param SlidingPagination $pagination
     * @param array|string      $groups
     *
     * @return OffsetPaginatedResponse
     */
    protected function buildPaginatedResponse(SlidingPagination $pagination, $groups)
    {
        $data = [];

        foreach ($pagination as $item) {
            $data[] = $item;
        }

        $this->get('mediamonks_rest_api.serializer.jms_groups')->setGroups($groups);

        $paginationData = $pagination->getPaginationData();
        $offset = $paginationData['firstItemNumber'] - 1;
        $limit = $paginationData['numItemsPerPage'];
        $total = $paginationData['totalCount'];

        return new OffsetPaginatedResponse($data, $offset, $limit, $total);
    }
}
