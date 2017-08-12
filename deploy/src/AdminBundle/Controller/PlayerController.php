<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use AdminBundle\Form\ConfirmMergePlayersType;
use AdminBundle\Utility\PlayerMerger;
use CoreBundle\Entity\Player;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function mergeAction(Request $request)
    {
        $player = $this->admin->getSubject();

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('The player could not be found');
        }

        $targetPlayer = $player->getTargetPlayer();

        if (!$targetPlayer instanceof Player) {
            throw new NotFoundHttpException('The target player could not be found');
        }

        $playerMerger = new PlayerMerger($player, $targetPlayer);

        $form = $this->createForm(ConfirmMergePlayersType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getEntityManager();
            $playerMerger->mergePlayers($entityManager, $this->getCache());
            $entityManager->flush();

            $this->addFlash('sonata_flash_success', 'The players were successfully merged.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $formGroupMessage = "Confirm merger of players '{$player->getGamerTag()}' and '{$targetPlayer->getGamerTag()}'";

        $this->admin->setFormGroups([
            'default' => [
                'name' => $formGroupMessage,
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['confirm', 'submit'],
            ],
        ]);

        return $this->render('AdminBundle:Player:confirm_merge.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
            'player' => $player,
            'playerMerger' => $playerMerger,
            'targetPlayer' => $targetPlayer,
        ]);
    }
}
