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
 * @Route(service="app.controller.default")
 */
class DefaultController extends AbstractDefaultController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Default:index.html.twig');
    }

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return Response
     *
     * @Route("/players/head-to-head/{playerOneId}/{playerTwoId}/", requirements={
     *  "playerOneId" = "\d+",
     *  "playerTwoId" = "\d+"
     * })
     *
     * @TODO Use slugs instead of IDs?
     */
    public function headToHeadAction($playerOneId, $playerTwoId)
    {
        $command = new HeadToHeadCommand($playerOneId, $playerTwoId);
        $record = $this->commandBus->handle($command);

        return $this->render('AppBundle:Players:head-to-head.html.twig', [
            'record' => $record,
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     *
     * @Route("/players/{slug}/")
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
}
