<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Result;
use CoreBundle\Entity\Set;
use Domain\Command\Player\DetailsCommand;
use Domain\Command\Player\OverviewCommand;
use Domain\Command\Player\ResultsCommand;
use Domain\Command\Player\SetsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/players", service="app.controller.player")
 */
class PlayerController extends AbstractDefaultController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="players_overview")
     */
    public function overviewAction(Request $request)
    {
        $tag = $request->get('tag');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($tag, $page, $limit);
        $pagination = $this->commandBus->handle($command);

        return $this->render('AppBundle:Players:overview.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/{slug}/", name="player_details")
     */
    public function detailsAction($slug)
    {
        $detailsCommand = new DetailsCommand($slug);
        $player = $this->commandBus->handle($detailsCommand);

        $setCommand = new SetsCommand($slug, 'tournament');
        $sets = $this->commandBus->handle($setCommand);

        $resultsCommand = new ResultsCommand($slug);
        $results = $this->commandBus->handle($resultsCommand);

        // TODO Move this to its own command.
        $combinedResults = [];

        /** @var Result $result */
        foreach ($results as $result) {
            $tournament = $result->getEvent()->getTournament();
            $name = $tournament->getName();
            $date = $tournament->getDateStart('Ymd');
            $resultSets = null;

            if (array_key_exists($name, $sets)) {
                $resultSets = $sets[$name];
            }

            $combinedResults[] = [
                'name'   => $name,
                'date'   => $date,
                'result' => $result,
                'sets'   => $resultSets,
            ];

            unset($sets[$name]);
        }

        /** @var Event[] $events */
        foreach ($sets as $tournamentId => $events) {
            $tournament = current($events)->getTournament();
            $name = $tournament->getName();
            $date = $tournament->getDateStart('Ymd');

            $combinedResults[] = [
                'name'   => $name,
                'date'   => $date,
                'result' => null,
                'sets'   => $events,
            ];
        }

        return $this->render('AppBundle:Players:details.html.twig', [
            'player'  => $player,
            'sets'    => $sets,
            'results' => $combinedResults,
        ]);
    }
}
