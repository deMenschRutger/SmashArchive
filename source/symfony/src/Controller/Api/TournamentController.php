<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Tournament\DetailsCommand;
use App\Bus\Command\Tournament\OverviewCommand;
use App\Bus\Command\Tournament\RanksCommand;
use App\Entity\Tournament;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/tournaments")
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
     * @Sensio\Route("/", name="api_tournaments_overview")
     * @Sensio\Method("GET")
     */
    public function indexAction(Request $request)
    {
        $name = $request->query->get('name');
        $location = $request->query->get('location');
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new OverviewCommand($name, $location, $page, $limit, 'dateStart', 'desc');
        $pagination = $this->bus->handle($command);

        $this->setSerializationGroups('tournaments_overview');

        return $this->buildPaginatedResponse($pagination);
    }

    /**
     * @param string $slug
     *
     * @return Tournament
     *
     * @Sensio\Route("/{slug}/", name="api_tournaments_details")
     * @Sensio\Method("GET")
     */
    public function detailsAction($slug)
    {
        $this->setSerializationGroups('tournaments_details');

        $command = new DetailsCommand($slug);

        return $this->bus->handle($command);
    }

    /**
     * @param int $eventId
     *
     * @return array
     *
     * @Sensio\Route("/events/{eventId}/ranks/", name="api_tournaments_ranks")
     * @Sensio\Method("GET")
     */
    public function ranksAction($eventId)
    {
        $this->setSerializationGroups('tournaments_results');

        $command = new RanksCommand(null, intval($eventId));

        return $this->bus->handle($command);
    }
}
