<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\HeadToHeadCommand;
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

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return Response
     *
     * @Route("/players/head-to-head/{playerOneId}/{playerTwoId}", requirements={
     *  "playerOneId" = "\d+",
     *  "playerTwoId" = "\d+"
     * })
     *
     * @TODO Use slugs instead of IDs?
     */
    public function headToHeadAction($playerOneId, $playerTwoId)
    {
        $command = new HeadToHeadCommand($playerOneId, $playerTwoId);
        $record = $this->commandBus->handle($command);

        return $this->render('AppBundle:Players:head-to-head.html.twig', [
            'record' => $record,
        ]);
    }
}
