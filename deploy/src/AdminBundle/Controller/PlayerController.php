<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use CoreBundle\Entity\Player;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerController extends Controller
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

        $form = $this
            ->createFormBuilder([
                'targetPlayer' => null,
            ])
            ->add('targetPlayer', EntityType::class, [
                'class' => 'CoreBundle:Player',
                'label' => false,
                'multiple' => false,
                'placeholder' => 'Please select a player',
                'choice_label' => 'expandedGamerTag',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Merge players',
            ])
            ->getForm()
        ;

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
        $targetPlayer = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Player')->findOneBy([
            'id' => $targetId,
        ]);

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('The player could not be found');
        }

        if (!$targetPlayer instanceof Player) {
            throw new NotFoundHttpException('The target player could not be found');
        }

        $form = $this
            ->createFormBuilder([])
            ->add('confirm', CheckboxType::class, [
                'label' => "I confirm that I'm aware of the consequences of merging these two players.",
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Merge players',
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // TODO Actually merge the two players.

            $this->addFlash('sonata_flash_success', 'The players were successfully merged.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $this->admin->setFormGroups([
            'default' => [
                'name' => "Confirm merger of players '{$player->getGamerTag()}' and '{$targetPlayer->getGamerTag()}'",
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
            'targetPlayer' => $targetPlayer,
        ]);
    }
}
