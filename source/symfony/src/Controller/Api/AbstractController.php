<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\RestApi\Response\KnpPaginatedResponse;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use MediaMonks\RestApi\Exception\FormValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     * @param string  $type
     * @param mixed   $entity
     * @param bool    $clearMissing
     */
    protected function validateForm(Request $request, string $type, $entity, $clearMissing = false)
    {
        $form = $this->createForm($type, $entity);
        $form->submit($request->request->all(), $clearMissing);

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }
    }

    /**
     * @param array|string $groups
     */
    protected function setSerializationGroups($groups)
    {
        $this->get('mediamonks_rest_api.serializer.jms_groups')->setGroups($groups);
    }

    /**
     * @param SlidingPagination $pagination
     *
     * @return KnpPaginatedResponse
     */
    protected function buildPaginatedResponse(SlidingPagination $pagination)
    {
        return new KnpPaginatedResponse($pagination);
    }
}
