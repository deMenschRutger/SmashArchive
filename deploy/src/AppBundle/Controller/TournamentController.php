<?php

declare(strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Form\TournamentsType;
use CoreBundle\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use CoreBundle\Bracket\DoubleElimination\Bracket as DoubleEliminationBracket;
use CoreBundle\Bracket\RoundRobin\Bracket as RoundRobinBracket;
use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Repository\PhaseGroupRepository;
use Domain\Command\Tournament\DetailsCommand;
use Domain\Command\Tournament\OverviewCommand;
use Domain\Command\Tournament\ResultsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/tournaments", service="app.controller.tournament")
 */
class TournamentController extends AbstractDefaultController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="tournaments_overview")
     */
    public function indexAction(Request $request)
    {
        $name = $request->get('name');
        $location = $request->get('location');
        $page = $request->get('page');
        $limit = $request->get('limit');

        $form = $this->createForm(TournamentsType::class, [
            'name' => $name,
            'location' => $location,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = $form->getData();

            return $this->redirectToRoute('tournaments_overview', [
                'name' => $parameters['name'],
                'location' => $parameters['location'],
            ]);
        }

        $command = new OverviewCommand($name, $location, $page, $limit, 'dateStart', 'desc');
        $pagination = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:overview.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/{slug}", name="tournaments_details")
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug, true);
        $tournament = $this->commandBus->handle($command);

        $command = new ResultsCommand($tournament->getId());
        $results = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:details.html.twig', [
            'results' => $results,
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/{slug}/brackets", name="tournaments_brackets")
     */
    public function bracketsAction($slug)
    {
        $command = new DetailsCommand($slug, false);
        $tournament = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:brackets.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param string $phaseGroupId
     * @return Response
     *
     * @Route("/{slug}/brackets/{phaseGroupId}", name="tournaments_brackets_details")
     */
    public function bracketDetailsAction($phaseGroupId)
    {
        /** @var PhaseGroupRepository $repository */
        $repository = $this->getRepository('CoreBundle:PhaseGroup');
        $phaseGroup = $repository->findWithTournament($phaseGroupId);

        if (!$phaseGroup instanceof PhaseGroup) {
            throw new NotFoundHttpException('The phase group could not be found.');
        }

        $tournament = $phaseGroup->getPhase()->getEvent()->getTournament();
        $bracket = null;
        $template = 'not-supported';

        switch ($phaseGroup->getType()) {
            case PhaseGroup::TYPE_SINGLE_ELIMINATION:
                $bracket = new SingleEliminationBracket($phaseGroup);
                $template = 'single-elimination';
                break;

            case PhaseGroup::TYPE_DOUBLE_ELIMINATION:
                $bracket = new DoubleEliminationBracket($phaseGroup);
                $template = 'double-elimination';
                break;

            case PhaseGroup::TYPE_ROUND_ROBIN:
                $bracket = new RoundRobinBracket($phaseGroup);
                $template = 'round-robin';
                break;
        }

        $template = "AppBundle:Tournaments/brackets:{$template}.html.twig";

        return $this->render($template, [
            'bracket'    => $bracket,
            'phaseGroup' => $phaseGroup,
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param string $slug
     * @param string $eventId
     * @return Response
     *
     * @Route("/{slug}/results/{eventId}", name="tournaments_results")
     *
     * @TODO Only retrieve the results of the selected event instead of results for all tournament events.
     */
    public function resultsAction($slug, $eventId)
    {
        $command = new DetailsCommand($slug, true);
        $tournament = $this->commandBus->handle($command);

        $command = new ResultsCommand($tournament->getId());
        $results = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:results.html.twig', [
            'eventId' => $eventId,
            'results' => $results,
            'tournament' => $tournament,
        ]);
    }
}
