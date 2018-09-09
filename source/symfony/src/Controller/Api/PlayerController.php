<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Player\DetailsCommand;
use App\Bus\Command\Player\HeadToHeadCommand;
use App\Bus\Command\Player\OverviewCommand;
use App\Bus\Command\Player\RanksCommand;
use App\Bus\Command\Player\SetsCommand;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/players")
 */
class PlayerController extends AbstractController
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
     * @Sensio\Route("/", name="api_players_overview")
     */
    public function indexAction(Request $request)
    {
        $tag = $request->query->get('tag');
        $location = $request->query->get('location');
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new OverviewCommand($tag, $location, $page, $limit);
        $pagination = $this->bus->handle($command);

        $this->setSerializationGroups('players_overview');

        return $this->buildPaginatedResponse($pagination);
    }

    /**
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Route("/{slug}/", name="api_players_details")
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_details');

        return $sets;
    }

    /**
     * @param Request $request
     * @param string  $slug
     *
     * @return array|OffsetPaginatedResponse
     *
     * @Sensio\Route("/{slug}/sets/", name="api_players_sets")
     */
    public function setsAction(Request $request, $slug)
    {
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new SetsCommand($slug, null, false, $page, $limit);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_sets');

        return $this->buildPaginatedResponse($sets);
    }

    /**
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Route("/{slug}/ranks/", name="api_players_ranks")
     *
     * @TODO Add pagination.
     */
    public function ranksAction($slug)
    {
        $command = new RanksCommand($slug);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_ranks');

        return $sets;
    }

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     *
     * @return array
     *
     * @Sensio\Route("/{playerOneSlug}/head-to-head/{playerTwoSlug}/", name="api_players_head_to_head")
     */
    public function headToHeadAction($playerOneSlug, $playerTwoSlug)
    {
        $command = new HeadToHeadCommand($playerOneSlug, $playerTwoSlug);

        return $this->bus->handle($command);
    }
}
