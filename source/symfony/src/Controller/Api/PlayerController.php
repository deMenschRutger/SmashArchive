<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Player\OverviewCommand;
use App\Bus\Command\Player\SetsCommand;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/api/players")
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
     * @Route("/", name="api_players_overview")
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
     * @param Request $request
     * @param string  $slug
     *
     * @return array|OffsetPaginatedResponse
     *
     * @Route("/{slug}/sets/", name="api_players_sets")
     *
     * @TODO This endpoint never seems to return any sets.
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
}
