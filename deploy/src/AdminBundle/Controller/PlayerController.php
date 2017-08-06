<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use AdminBundle\Form\ConfirmMergePlayersType;
use AdminBundle\Form\MergePlayersType;
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

        $form = $this->createForm(MergePlayersType::class, [
            'targetPlayer' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Player $targetPlayer */
            $targetPlayer = $form->getData()['targetPlayer'];

            return new RedirectResponse($this->admin->generateUrl('confirm_merge', [
                'id' => $player->getId(),
                'targetId' => $targetPlayer->getId(),
            ]));
        }

        $this->admin->setFormGroups([
            'default' => [
                'name' => 'Select player to merge with',
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['targetPlayer', 'submit'],
            ],
        ]);

        return $this->render('AdminBundle:Player:merge.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
            'player' => $player,
        ]);
    }

    /**
     * @param Request $request
     * @param string  $targetId
     * @return Response
     */
    public function confirmMergeAction(Request $request, $targetId)
    {
        $player = $this->admin->getSubject();
        $targetPlayer = $this->getRepository('CoreBundle:Player')->findOneBy([
            'id' => $targetId,
        ]);

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('The player could not be found');
        }

        if (!$targetPlayer instanceof Player) {
            throw new NotFoundHttpException('The target player could not be found');
        }

        $playerMerger = new PlayerMerger($player, $targetPlayer);

        $form = $this->createForm(ConfirmMergePlayersType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // TODO Actually merge the two players.

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
