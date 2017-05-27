<?php

declare(strict_types = 1);

namespace AppBundle\Controller;

use CoreBundle\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use CoreBundle\Bracket\DoubleElimination\Bracket as DoubleEliminationBracket;
use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Repository\PhaseGroupRepository;
use Domain\Command\Tournament\DetailsCommand;
use Domain\Command\Tournament\OverviewCommand;
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
        $page = $request->get('page');
        $limit = $request->get('limit');

        $command = new OverviewCommand($name, $page, $limit, 'dateStart', 'desc');
        $pagination = $this->commandBus->handle($command);

        return $this->render('AppBundle:Tournaments:overview.html.twig', [
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

        return $this->render('AppBundle:Tournaments:details.html.twig', [
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
        return new Response('This will contain an overview of the brackets for this tournament');
    }

    /**
     * @param string $phaseGroupId
     * @return Response
     *
     * @Route("/{slug}/brackets/{phaseGroupId}", name="tournaments_brackets_details")
     */
    public function bracketDetailAction($phaseGroupId)
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
        }

        $template = "AppBundle:Tournaments/brackets:{$template}.html.twig";

        return $this->render($template, [
            'bracket'    => $bracket,
            'tournament' => $tournament,
        ]);
    }
}
