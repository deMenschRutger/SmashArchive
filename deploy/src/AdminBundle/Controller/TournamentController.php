<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use CoreBundle\Entity\Tournament;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentController extends Controller
{
    /**
     * @return Response
     */
    public function importAction()
    {
        $tournament = $this->admin->getSubject();

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found');
        }

        $error = null;

        if ($tournament->getSource() === Tournament::SOURCE_CUSTOM) {
            $error = "The source for this tournament is 'custom', therefore it can not be imported.";
        } elseif ($tournament->getSource() !== Tournament::SOURCE_SMASHGG) {
            $error = "Only tournaments with the source 'smash.gg' can be imported at this time.";
        }

        return $this->render('AdminBundle:Tournament:import.html.twig', [
            'error' => $error,
            'tournament' => $tournament,
        ]);
    }
}
