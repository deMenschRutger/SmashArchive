<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AbstractController extends Controller
{
    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        /** @var JWTUser $jwtUser */
        $jwtUser = $this->container->get('security.token_storage')->getToken()->getUser();

        return $this->getRepository('App:User')->find($jwtUser->getUsername());
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    protected function getRepository($entityName): EntityRepository
    {
        return $this->container->get('doctrine.orm.entity_manager')->getRepository($entityName);
    }
}
