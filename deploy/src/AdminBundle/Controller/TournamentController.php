<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use AdminBundle\Form\ConfirmGenerateResultsType;
use AdminBundle\Form\ImportChallongeType;
use AdminBundle\Form\ImportSmashggType;
use CoreBundle\Entity\Tournament;
use Domain\Command\WorkQueue\AddJobCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function importAction(Request $request)
    {
        $tournament = $this->admin->getSubject();

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found');
        }

        if ($tournament->getSource() === Tournament::SOURCE_SMASHGG) {
            return $this->importSmashgg($request, $tournament);
        } elseif ($tournament->getSource() === Tournament::SOURCE_CHALLONGE) {
            return $this->importChallonge($request, $tournament);
        } elseif ($tournament->getSource() === Tournament::SOURCE_CUSTOM) {
            return $this->renderError("The source for this tournament is 'custom', therefore it can not be imported.", $tournament);
        } else {
            $message = sprintf("Tournaments with the source '%s' can not be imported at this time.", $tournament->getSource());

            return $this->renderError($message, $tournament);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function resultsAction(Request $request)
    {
        $tournament = $this->admin->getSubject();

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found');
        }

        $form = $this->createForm(ConfirmGenerateResultsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($tournament->getEvents() as $event) {
                $name = "Generate results for event #{$event->getId()} of tournament {$tournament->getName()}";
                $job = [
                    'type' => AddJobCommand::TYPE_GENERATE_RESULTS,
                    'eventId' => $event->getId(),
                ];

                $command = new AddJobCommand('generate-results', $name, $job);
                $this->handleCommand($command);
            }

            $this->addFlash(
                'sonata_flash_success',
                'The generate results job was added to the queue and will be processed shortly'
            );

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $formGroupMessage = "Confirm generating results for tournament '{$tournament->getName()}'";

        $this->admin->setFormGroups([
            'default' => [
                'name' => $formGroupMessage,
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['confirm', 'submit'],
            ],
        ]);

        return $this->render('AdminBundle:Tournament:confirm_results.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @return RedirectResponse|Response
     */
    protected function importSmashgg(Request $request, Tournament $tournament)
    {
        $smashggId = $tournament->getSmashggIdFromUrl();

        if (!$smashggId) {
            return $this->renderError('Could not extract a tournament ID from the provided smash.gg url.', $tournament);
        }

        $form = $this->createForm(
            ImportSmashggType::class,
            [ 'events' => [] ],
            [ 'smashggId' => $smashggId ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournament->setExternalId($smashggId);
            $data = $form->getData();

            return $this->addImportJob($tournament, Tournament::SOURCE_SMASHGG, [
                'smashggId' => $smashggId,
                'events' => $data['events'],
            ]);
        }

        $this->admin->setFormGroups([
            'default' => [
                'name' => 'Select events to import',
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['events', 'submit'],
            ],
        ]);

        return $this->render('AdminBundle:Tournament:import.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @return Response
     */
    protected function importChallonge(Request $request, Tournament $tournament)
    {
        $form = $this->createForm(ImportChallongeType::class);
        $form->handleRequest($request);

        $this->admin->setFormGroups([
            'default' => [
                'name' => 'Confirmation',
                'description' => null,
                'box_class' => 'box box-primary',
                'translation_domain' => null,
                'fields' => ['confirm', 'submit'],
            ],
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->addImportJob($tournament, Tournament::SOURCE_CHALLONGE, [
                'slug' => $tournament->getSlug(),
            ]);
        }

        return $this->render('AdminBundle:Tournament:import.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
            'tournament' => $tournament,
        ]);
    }

    /**
     * @param Tournament $tournament
     * @param string     $source
     * @param array      $options
     * @return Response
     */
    protected function addImportJob(Tournament $tournament, $source, array $options)
    {
        $name = "Import tournament {$tournament->getName()}";
        $job = array_merge([
            'type' => AddJobCommand::TYPE_IMPORT_TOURNAMENT,
            'source' => $source,
        ], $options);

        $command = new AddJobCommand('import-tournament', $name, $job);
        $this->handleCommand($command);

        $this->addFlash(
            'sonata_flash_success',
            'The tournament import job was added to the queue and will be processed shortly'
        );

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @param string     $error
     * @param Tournament $tournament
     * @return Response
     */
    protected function renderError(string $error, Tournament $tournament)
    {
        return $this->render('AdminBundle:Tournament:import_error.html.twig', [
            'error' => $error,
            'tournament' => $tournament,
        ]);
    }
}
