<?php

declare(strict_types=1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route(service="api.controller.default")
 */
class DefaultController extends AbstractDefaultController
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return [];
    }
}
