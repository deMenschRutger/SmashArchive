<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Tournament\DetailsCommand;
use App\Bus\Command\Tournament\OverviewCommand;
use App\Bus\Command\Tournament\StandingsCommand;
use App\Entity\Rank;
use App\Entity\Tournament;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;
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
     * Returns a list of tournaments.
     *
     * @param Request $request
     *
     * @return PaginatedResponseInterface
     *
     * @Sensio\Route("/", name="api_tournaments_overview")
     * @Sensio\Method("GET")
     *
     * @SWG\Tag(name="Tournaments")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the tournaments were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Tournament::class, groups={"tournaments_overview"}))
     *     )
     * )
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
     * Returns detailed information about an individual tournament.
     *
     * @param string $slug
     *
     * @return Tournament
     *
     * @Sensio\Route("/{slug}/", name="api_tournaments_details")
     * @Sensio\Method("GET")
     *
     * @SWG\Tag(name="Tournaments")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the tournament was successfully retrieved.",
     *     @SWG\Items(ref=@Model(type=Tournament::class, groups={"tournaments_details"}))
     * )
     */
    public function detailsAction($slug)
    {
        $this->setSerializationGroups('tournaments_details');

        $command = new DetailsCommand($slug);

        return $this->bus->handle($command);
    }

    /**
     * Returns the standings of a single tournament event.
     *
     * @param int $eventId
     *
     * @return array
     *
     * @Sensio\Route("/events/{eventId}/standings/", name="api_tournaments_standings")
     * @Sensio\Method("GET")
     *
     * @SWG\Tag(name="Tournaments")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the standings were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Rank::class, groups={"tournaments_standings"}))
     *     )
     * )
     */
    public function standingsAction($eventId)
    {
        $this->setSerializationGroups('tournaments_standings');

        $command = new StandingsCommand(null, intval($eventId));

        return $this->bus->handle($command);
    }
}
