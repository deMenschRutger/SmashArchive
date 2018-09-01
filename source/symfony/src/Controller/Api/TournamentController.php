<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Tournament\OverviewCommand;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/api/tournaments")
 */
class TournamentController extends AbstractController
{
    /**
     * @param Request $request
     *
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
        $pagination = $this->getCommandBus()->handle($command);

        return $this->buildPaginatedResponse($pagination, 'tournaments_overview');
    }
}
