<?php

declare(strict_types=1);

namespace ApiBundle\Controller;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\SetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/head-to-head/{playerOne}/{playerTwo}")
     *
     * @TODO Add route requirements.
     * @TODO Use slugs instead of IDs?
     */
    public function headToHeadAction($playerOne, $playerTwo)
    {
        /** @var SetRepository $repository */
        $repository = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Set');
        $sets = $repository->findHeadToHeadSets(intval($playerOne), intval($playerTwo));

        $playerOneScore = 0;
        $playerTwoScore = 0;

        foreach ($sets as $set) {
            /** @var Set $set */
            $winnerId = $set->getWinner()->getPlayers()->first()->getId();

            if ($winnerId == $playerOne) {
                $playerOneScore += 1;
            } elseif ($winnerId == $playerTwo) {
                $playerTwoScore += 1;
            }
        }

        return [
            $playerOne => $playerOneScore,
            $playerTwo => $playerTwoScore,
        ];
    }
}
