<?php

declare(strict_types=1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Player\HeadToHeadCommand;
use Domain\Command\Player\OverviewCommand;
use Domain\Command\Player\ResultsCommand;
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
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($tag, $page, $limit);
        $result = $this->commandBus->handle($command);

        return $this->buildPaginatedResponse($result['players'], $result['pagination']);
    }

    /**
     * @param int $playerId
     * @return array
     *
     * @Route("/players/{playerId}/results/", requirements={
     *  "playerId" = "\d+",
     * })
     *
     * @TODO Use slugs instead of IDs
     */
    public function resultsAction($playerId)
    {
        $command = new ResultsCommand($playerId);

        return $this->commandBus->handle($command);
    }

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return array
     *
     * @Route("/players/head-to-head/{playerOneId}/{playerTwoId}/", requirements={
     *  "playerOneId" = "\d+",
     *  "playerTwoId" = "\d+"
     * })
     *
     * @TODO Use slugs instead of IDs
     */
    public function headToHeadAction($playerOneId, $playerTwoId)
    {
        $command = new HeadToHeadCommand($playerOneId, $playerTwoId);

        return $this->commandBus->handle($command);
    }
}
