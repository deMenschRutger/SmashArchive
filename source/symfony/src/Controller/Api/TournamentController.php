<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Tournament\DetailsCommand;
use App\Bus\Command\Tournament\OverviewCommand;
use App\Bus\Command\Tournament\StandingsCommand;
use App\Entity\Rank;
use App\Entity\Tournament;
use App\Form\TournamentType;
use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Pheanstalk\Pheanstalk;
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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @var Pheanstalk
     */
    protected $pheanstalk;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CommandBus             $bus
     * @param Pheanstalk             $pheanstalk
     */
    public function __construct(EntityManagerInterface $entityManager, CommandBus $bus, Pheanstalk $pheanstalk)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->pheanstalk = $pheanstalk;
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
        $command = new DetailsCommand($slug);
        $tournament = $this->bus->handle($command);

        $this->setSerializationGroups('tournaments_details');

        return $tournament;
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
        $command = new StandingsCommand(null, intval($eventId));
        $standings = $this->bus->handle($command);

        $this->setSerializationGroups('tournaments_standings');

        return $standings;
    }

    /**
     * @return bool
     *
     * @Sensio\Method("POST")
     * @Sensio\Route("/", name="api_tournaments_import")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Tournaments")
     */
    public function importAction()
    {
        // TODO Create the job.
        $this->pheanstalk->put(\GuzzleHttp\json_encode([
            'type'   => 'tournament-import',
            'source' => 'smashgg',
            'events' => [],
        ]));

        return true;
    }

    /**
     * Updates specific properties of an existing tournament.
     *
     * @param Request $request
     * @param string  $slug
     *
     * @return Tournament
     *
     * @Sensio\Method("PATCH")
     * @Sensio\Route("/{slug}/", name="api_tournaments_update")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Tournaments")
     * @SWG\Parameter(
     *     in="body",
     *     name="status",
     *     @Model(type=TournamentType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the tournament details were successfully updated.",
     *     @SWG\Items(ref=@Model(type=Tournament::class, groups={"tournaments_details"}))
     * )
     */
    public function updateAction(Request $request, $slug)
    {
        $command = new DetailsCommand($slug);
        $tournament = $this->bus->handle($command);

        $this->validateForm($request, TournamentType::class, $tournament);

        $this->entityManager->flush();

        $this->setSerializationGroups('tournaments_details');

        return $tournament;
    }

    /**
     * Deletes an existing tournament.
     *
     * @param string $slug
     *
     * @return bool
     *
     * @Sensio\Method("DELETE")
     * @Sensio\Route("/{slug}/", name="api_tournaments_delete")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Tournaments")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the tournament was successfully deleted."
     * )
     */
    public function deleteAction($slug)
    {
        $command = new DetailsCommand($slug);
        $tournament = $this->bus->handle($command);

        $this->entityManager->remove($tournament);
        $this->entityManager->flush();

        return true;
    }
}
