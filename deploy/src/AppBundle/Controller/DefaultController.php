<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Repository\PlayerRepository;
use Domain\Command\Player\HeadToHeadCommand;
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
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Default:index.html.twig');
    }
}
