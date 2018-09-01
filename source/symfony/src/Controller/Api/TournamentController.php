<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Tournament\OverviewCommand;
use League\Tactician\CommandBus;
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
     * @var CommandBus
     */
    protected $bus;

    /**
     * @param CommandBus $bus
     */
    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }
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
        $pagination = $this->bus->handle($command);

        return $this->buildPaginatedResponse($pagination, 'tournaments_overview');
    }
}
