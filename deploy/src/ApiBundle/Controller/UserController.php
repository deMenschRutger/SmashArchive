<?php

declare(strict_types = 1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/users", service="api.controller.user")
 */
class UserController extends AbstractDefaultController
{
    /**
     * @return array
     *
     * @Route("/login/")
     */
    public function loginAction()
    {
        return [];
    }
}
