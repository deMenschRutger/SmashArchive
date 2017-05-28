<?php

declare(strict_types = 1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Tournament\DetailsCommand;
use Domain\Command\Tournament\OverviewCommand;
use Domain\Command\Tournament\ResultsCommand;
use MediaMonks\RestApiBundle\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/tournaments", service="api.controller.tournament")
 */
class TournamentController extends AbstractDefaultController
{
    /**
     * @param Request $request
     * @return PaginatedResponseInterface
     *
     * @Route("/", name="api_tournaments_overview")
     */
    public function indexAction(Request $request)
    {
        $name = $request->get('name');
        $location = $request->get('location');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($name, $location, $page, $limit, 'dateStart', 'desc');
        $pagination = $this->commandBus->handle($command);

        return $this->buildPaginatedResponse($pagination, 'tournaments_overview');
    }

    /**
     * @param string $slug
     * @return array
     *
     * @Route("/{slug}", name="api_tournaments_details")
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug);
        $tournament = $this->commandBus->handle($command);

        return $this->buildResponse($tournament, 'tournaments_details');
    }

    /**
     * @param int $eventId
     * @return array
     *
     * @Route("/events/{eventId}/results/", name="api_tournaments_results")
     */
    public function resultsAction($eventId)
    {
        $command = new ResultsCommand($eventId);
        $tournament = $this->commandBus->handle($command);

        return $this->buildResponse($tournament, 'tournaments_results');
    }
}
