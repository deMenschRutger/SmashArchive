<?php

declare(strict_types = 1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Facebook\Facebook;
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
        $facebook = new Facebook([
            'app_id' => $this->getParameter('facebook_app_id'),
            'app_secret' => $this->getParameter('facebook_app_secret'),
            'default_graph_version' => 'v3.0',
        ]);

        return [];
    }
}
