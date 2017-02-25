<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use CoreBundle\Repository\PlayerRepository;
use Domain\Command\Player\HeadToHeadCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/players", service="app.controller.player")
 */
class PlayerController extends AbstractDefaultController
{
    /**
     * @Route("/", name="players_overview")
     */
    public function indexAction()
    {
        $players = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('CoreBundle:Player')
            ->findBy([], null, 50)
        ;

        return $this->render('AppBundle:Players:overview.html.twig', [
            'players' => $players,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/{slug}/", name="player_details")
     */
    public function playerAction($slug)
    {
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Player');

        $sets = $playerRepository->findSetsBySlug($slug);
        $setsByTournament = [];

        foreach ($sets as $set) {
            $setsByTournament[$set['tournamentName']][] = $set;
        }

        return $this->render('AppBundle:Players:player.html.twig', [
            'setsByTournament' => $setsByTournament,
        ]);
    }

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return Response
     *
     * @Route("/head-to-head/{playerOneId}/{playerTwoId}/", requirements={
     *  "playerOneId" = "\d+",
     *  "playerTwoId" = "\d+"
     * })
     *
     * @TODO Use slugs instead of IDs
     */
    public function headToHeadAction($playerOneId, $playerTwoId)
    {
        $command = new HeadToHeadCommand($playerOneId, $playerTwoId);
        $record = $this->commandBus->handle($command);

        return $this->render('AppBundle:Players:head-to-head.html.twig', [
            'record' => $record,
        ]);
    }
}
