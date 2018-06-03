<?php

declare(strict_types = 1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Facebook\Facebook;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/users", service="api.controller.user")
 */
class UserController extends AbstractDefaultController
{
    /**
     * @param Request $request
     *
     * @return array
     *
     * @Route("/login/")
     */
    public function loginAction(Request $request)
    {
        $facebook = new Facebook([
            'app_id' => $this->getParameter('facebook_app_id'),
            'app_secret' => $this->getParameter('facebook_app_secret'),
            'default_graph_version' => 'v3.0',
        ]);

        $accessToken = $request->get('accessToken');
        $response = $facebook->get('/me', $accessToken);

        return [
            'accessToken' => 'customAccessToken',
        ];
    }
}
