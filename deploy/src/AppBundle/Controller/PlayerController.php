<?php

declare(strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Form\PlayersType;
use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\Player\DetailsCommand;
use Domain\Command\Player\OverviewCommand;
use Domain\Command\Player\ResultsCommand;
use Domain\Command\Player\SetsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;

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
        $location = $request->get('location');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $form = $this->createForm(PlayersType::class, [
            'tag' => $tag,
            'location' => $location,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = $form->getData();

            return $this->redirectToRoute('players_overview', [
                'tag' => $parameters['tag'],
                'location' => $parameters['location'],
            ]);
        }

        $command = new OverviewCommand($tag, $location, $page, $limit);
        $pagination = $this->commandBus->handle($command);

        return $this->render('AppBundle:Players:overview.html.twig', [
            'form' => $form->createView(),
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

        $setCommand = new SetsCommand($slug);
        $sets = $this->commandBus->handle($setCommand);

        $resultsCommand = new ResultsCommand($slug, $sets);
        $results = $this->commandBus->handle($resultsCommand);

        return $this->render('AppBundle:Players:details.html.twig', [
            'player'  => $player,
            'results' => $results,
        ]);
    }
}
