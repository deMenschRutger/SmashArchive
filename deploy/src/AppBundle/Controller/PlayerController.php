<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Entity\Set;
use Domain\Command\Player\DetailsCommand;
use Domain\Command\Player\OverviewCommand;
use Domain\Command\Player\ResultsCommand;
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
     *
     * @TODO Tournaments aren't sorted by date.
     */
    public function detailsAction($slug)
    {
        $detailsCommand = new DetailsCommand($slug);
        $player = $this->commandBus->handle($detailsCommand);

        $resultsCommand = new ResultsCommand($slug);
        $sets = $this->commandBus->handle($resultsCommand);
        $setsByTournament = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phase = $set->getPhaseGroup()->getPhase();
            $phaseName = $phase->getName();
            $eventName = $phase->getEvent()->getName();
            $tournamentName = $phase->getEvent()->getTournament()->getName();

            $setsByTournament[$tournamentName][$eventName][$phaseName][] = $set;
        }

        return $this->render('AppBundle:Players:details.html.twig', [
            'player' => $player,
            'setsByTournament' => $setsByTournament,
        ]);
    }
}
