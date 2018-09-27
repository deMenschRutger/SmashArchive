<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\Player;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/players")
 */
class PlayerController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return Player
     *
     * @Sensio\Method("PATCH")
     * @Sensio\Route("/{id}/", name="api_players_update")
     * @Sensio\IsGranted("ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $player = $this->entityManager->find('App:Player', $id);

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('The player could not be found.');
        }

        $this->validateForm($request, PlayerType::class, $player);

        $this->entityManager->flush();

        return $player;
    }
}
