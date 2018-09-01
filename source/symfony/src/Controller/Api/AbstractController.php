<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use League\Tactician\CommandBus;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AbstractController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CommandBus             $bus
     * @param TokenStorageInterface  $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, CommandBus $bus, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
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
     * @return CommandBus
     */
    protected function getCommandBus(): CommandBus
    {
        return $this->bus;
    }

    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        /** @var JWTUser $jwtUser */
        $jwtUser = $this->tokenStorage->getToken()->getUser();

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

        // TODO Why can't we inject this dependency?
        $this->get('mediamonks_rest_api.serializer.jms_groups')->setGroups($groups);

        $paginationData = $pagination->getPaginationData();
        $offset = $paginationData['firstItemNumber'] - 1;
        $limit = $paginationData['numItemsPerPage'];
        $total = $paginationData['totalCount'];

        return new OffsetPaginatedResponse($data, $offset, $limit, $total);
    }
}
