<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\Player;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;
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
     * Updates individual properties of a specific player.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Player
     *
     * @Sensio\Method("PATCH")
     * @Sensio\Route("/{id}/", name="api_players_update")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Players")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the countries were successfully retrieved.",
     *     @SWG\Items(ref=@Model(type=Player::class))
     * )
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
