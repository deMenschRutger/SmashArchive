<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use CoreBundle\Entity\Tournament;
use CoreBundle\Service\Smashgg\Smashgg;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     *
     * @TODO Refactor this method, it has too many responsibilities.
     */
    public function importAction(Request $request)
    {
        $tournament = $this->admin->getSubject();

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found');
        }

        if ($tournament->getSource() === Tournament::SOURCE_CUSTOM) {
            return $this->renderError("The source for this tournament is 'custom', therefore it can not be imported.", $tournament);
        } elseif ($tournament->getSource() !== Tournament::SOURCE_SMASHGG) {
            return $this->renderError("Only tournaments with the source 'smash.gg' can be imported at this time.", $tournament);
        }

        $smashggUrl = $tournament->getSmashggUrl();
        preg_match('~https?:\/\/smash\.gg\/tournament\/([0-9a-z-]+)\/~', $smashggUrl, $matches);

        if (!array_key_exists(1, $matches)) {
            return $this->renderError('Could not extract a tournament ID from the provided smash.gg url.', $tournament);
        }

        $smashggId = $matches[1];

        /** @var Smashgg $smashgg */
        $smashgg = $this->get('core.service.smashgg');
        $events = $smashgg->getTournamentEvents($smashggId, true);
        $choices = [];

        foreach ($events as $event) {
            $name = $event['name'];
            $choices[$name] = $event['id'];
        }

        $defaultData = [
            'events' => [],
        ];

        $form = $this
            ->createFormBuilder($defaultData)
            ->add('events', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'label' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add to queue',
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // TODO Add the job to the queue and redirect.
        }

        $this->admin->setFormGroups([
            'events' => [
                'name' => 'Select events to import',
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['events', 'submit'],
            ],
        ]);

        return $this->render('AdminBundle:Tournament:import.html.twig', [
            'admin' => $this->admin,
            'error' => null,
            'form' => $form->createView(),
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param string     $error
     * @param Tournament $tournament
     * @return Response
     */
    protected function renderError(string $error, Tournament $tournament)
    {
        return $this->render('AdminBundle:Tournament:import.html.twig', [
            'error' => $error,
            'tournament' => $tournament,
        ]);
    }
}
