<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Tournament\DetailsCommand;
use Domain\Command\Tournament\OverviewCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/tournaments", service="app.controller.tournament")
 */
class TournamentController extends AbstractDefaultController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="tournaments_overview")
     */
    public function indexAction(Request $request)
    {
        $name = $request->get('name');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($name, $page, $limit, 'dateStart', 'desc');
        $pagination = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:overview.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/{slug}", name="tournaments_details")
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug, true);
        $tournament = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:details.html.twig', [
            'tournament' => $tournament,
        ]);
    }
}
