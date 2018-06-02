<?php

declare(strict_types = 1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Facebook\Facebook;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route(service="app.controller.default")
 */
class DefaultController extends AbstractDefaultController
{
    /**
     * @return Response
     *
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $facebook = new Facebook([
            'app_id' => $this->getParameter('facebook_app_id'),
            'app_secret' => $this->getParameter('facebook_app_secret'),
            'default_graph_version' => 'v3.0',
        ]);

        return $this->render('AppBundle:Default:index.html.twig');
    }
}
