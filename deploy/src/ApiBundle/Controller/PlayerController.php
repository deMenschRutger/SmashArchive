<?php

declare(strict_types = 1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Player\HeadToHeadCommand;
use Domain\Command\Player\OverviewCommand;
use Domain\Command\Player\SetsCommand;
use MediaMonks\RestApiBundle\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/players", service="api.controller.player")
 */
class PlayerController extends AbstractDefaultController
{
    /**
     * @param Request $request
     * @return PaginatedResponseInterface
     *
     * @Route("/", name="api_players_overview")
     */
    public function indexAction(Request $request)
    {
        $tag = $request->get('tag');
        $location = $request->get('location');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($tag, $location, $page, $limit);
        $pagination = $this->commandBus->handle($command);

        return $this->buildPaginatedResponse($pagination, 'players_overview');
    }

    /**
     * @param string $slug
     * @return array
     *
     * @Route("/{slug}/sets/", name="api_players_sets")
     */
    public function setsAction($slug)
    {
        $command = new SetsCommand($slug);
        $sets = $this->commandBus->handle($command);

        return $this->buildResponse($sets, 'players_sets');
    }

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     * @return array
     *
     * @Route("/{playerOneSlug}/head-to-head/{playerTwoSlug}/", name="api_players_head_to_head")
     */
    public function headToHeadAction($playerOneSlug, $playerTwoSlug)
    {
        $command = new HeadToHeadCommand($playerOneSlug, $playerTwoSlug);

        return $this->commandBus->handle($command);
    }
}
