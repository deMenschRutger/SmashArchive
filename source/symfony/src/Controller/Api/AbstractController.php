<?php

declare(strict_types = 1);

namespace App\Controller\Api;

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
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }
}
