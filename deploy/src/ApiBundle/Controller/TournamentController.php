<?php

declare(strict_types=1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Tournament\OverviewCommand;
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
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($name, $page, $limit);
        $pagination = $this->commandBus->handle($command);

        return $this->buildPaginatedResponse($pagination, 'tournaments_overview');
    }

    /**
     * @param string $slug
     * @param int    $eventId
     * @return array
     *
     * @Route("/{slug}/event/{eventId}/results/")
     */
    public function resultsAction($slug, $eventId)
    {
        return [];
    }
}
