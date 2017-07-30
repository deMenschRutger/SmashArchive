<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg;

use CoreBundle\Entity\Country;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractImporter;
use CoreBundle\Importer\Smashgg\Processor\EntrantProcessor;
use CoreBundle\Importer\Smashgg\Processor\EventProcessor;
use CoreBundle\Importer\Smashgg\Processor\GameProcessor;
use CoreBundle\Importer\Smashgg\Processor\PhaseGroupProcessor;
use CoreBundle\Importer\Smashgg\Processor\PhaseProcessor;
use CoreBundle\Importer\Smashgg\Processor\PlayerProcessor;
use CoreBundle\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer extends AbstractImporter
{
    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @var GameProcessor
     */
    protected $gameProcessor;

    /**
     * @var EventProcessor
     */
    protected $eventProcessor;

    /**
     * @var PhaseProcessor
     */
    protected $phaseProcessor;

    /**
     * @var PhaseGroupProcessor
     */
    protected $phaseGroupProcessor;

    /**
     * @var PlayerProcessor
     */
    protected $playerProcessor;

    /**
     * @var EntrantProcessor
     */
    protected $entrantProcessor;

    /**
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     * @param Smashgg       $smashgg
     */
    public function __construct(SymfonyStyle $io, EntityManager $entityManager, Smashgg $smashgg)
    {
        $this->setIo($io);
        $this->setEntityManager($entityManager);
        $this->smashgg = $smashgg;
    }

    /**
     * @param string $smashggId
     * @param array  $eventIds
     */
    public function import($smashggId, $eventIds)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $this->io->writeln('Retrieving tournament...');
        $this->tournament = $this->getTournament($smashggId);

        $this->io->writeln('Processing games...');
        $this->gameProcessor = $this->processGames();

        $this->io->writeln('Processing events...');
        $this->eventProcessor = $this->processEvents($eventIds);

        $this->io->writeln('Processing phases...');
        $this->phaseProcessor = $this->processPhases();

        $phaseIds = array_keys($this->phaseProcessor->getAllPhases());
        $groups = $this->smashgg->getTournamentGroups($this->tournament->getSmashggSlug(), $phaseIds);

        $this->phaseGroupProcessor = $this->processPhaseGroups($groups);
        $this->playerProcessor = $this->processPlayers($groups);

        $this->io->writeln('Processing entrants...');
        $this->entrantProcessor = $this->processEntrants($groups);

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    protected function getTournament($slug)
    {
        $smashggTournament = $this->smashgg->getTournament($slug);
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'smashggSlug' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSmashggSlug($slug);
            $tournament->setIsActive(true);
            $tournament->setIsComplete(true);

            $this->entityManager->persist($tournament);
        }

        $dateStart = new \DateTime();
        $dateStart->setTimestamp($smashggTournament['startAt']);

        $tournament->setName($smashggTournament['name']);
        $tournament->setCity($smashggTournament['city']);
        $tournament->setCountry($this->findCountry(null, $smashggTournament['countryCode']));
        $tournament->setDateStart($dateStart);

        return $tournament;
    }

    /**
     * @param string $name
     * @param string $code
     * @return Country|null
     */
    protected function findCountry($name, $code = null)
    {
        $countryRepository = $this->getRepository('CoreBundle:Country');

        if ($code) {
            $country = $countryRepository->findOneBy([
                'code' => $code,
            ]);

            if ($country instanceof Country) {
                return $country;
            }
        }

        $country = $countryRepository->findOneBy([
            'name' => $name,
        ]);

        if ($country instanceof Country) {
            return $country;
        }

        $name = 'The '.$name;

        return $countryRepository->findOneBy([
            'name' => $name,
        ]);
    }

    /**
     * @return GameProcessor
     */
    protected function processGames()
    {
        $games = $this->smashgg->getTournamentVideogames($this->tournament->getSmashggSlug(), true);
        $processor = new GameProcessor($this->entityManager);

        foreach ($games as $gameData) {
            $processor->processNew($gameData);
        }

        return $processor;
    }

    /**
     * @param array $eventIds
     * @return EventProcessor
     */
    protected function processEvents(array $eventIds)
    {
        $events = $this->smashgg->getTournamentEvents($this->tournament->getSmashggSlug(), true);
        $events = array_filter($events, function ($event) use ($eventIds) {
            return in_array($event['id'], $eventIds);
        });

        $processor = new EventProcessor($this->entityManager);

        foreach ($events as $eventData) {
            $game = $this->gameProcessor->findGame($eventData['videogameId']);
            $processor->processNew($eventData, $this->tournament, $game);
        }

        $processor->cleanUp($this->tournament);

        return $processor;
    }

    /**
     * @return PhaseProcessor
     */
    protected function processPhases()
    {
        $phases = $this->smashgg->getTournamentPhases($this->tournament->getSmashggSlug());
        $processor = new PhaseProcessor($this->entityManager);

        foreach ($phases as $phaseData) {
            $event = $this->eventProcessor->findEvent($phaseData['eventId']);

            if (!$event instanceof Event) {
                // This probably means the event was not selected for importing.
                continue;
            }

            $processor->processNew($phaseData, $event);
        }

        $processor->cleanUp($this->tournament);

        return $processor;
    }

    /**
     * @param array $groups
     * @return PhaseGroupProcessor
     */
    protected function processPhaseGroups(array $groups)
    {
        $counter = count($groups);

        $this->io->writeln(sprintf('Processing %d groups...', $counter));
        $this->io->newLine();
        $this->io->progressStart($counter);

        $processor = new PhaseGroupProcessor($this->entityManager);

        foreach ($groups as $phaseGroupData) {
            $phase = $this->phaseProcessor->findPhase($phaseGroupData['phaseId']);

            if (!$phase instanceof Phase) {
                continue;
            }

            $processor->processNew($phaseGroupData, $phase);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->newLine();

        $processor->cleanUp($this->tournament);

        return $processor;
    }

    /**
     * @param array $groups
     * @return PlayerProcessor
     */
    protected function processPlayers(array $groups)
    {
        $counter = count($groups);

        $this->io->writeln('Processing players...');
        $this->io->newLine();
        $this->io->progressStart($counter);

        $processor = new PlayerProcessor($this->entityManager);

        foreach ($groups as $phaseGroupData) {
            $players = $this->smashgg->getPhaseGroupPlayers($phaseGroupData['id']);

            foreach ($players as $playerData) {
                $country = $this->findCountry($playerData['country']);
                $processor->processNew($playerData, $country);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->newLine();

        // We need to flush the entity manager here, otherwise the next event won't find new players created in
        // previous events associated with this tournament.
        //$this->io->writeln('Flushing the entity manager...');
        //$this->entityManager->flush();

        return $processor;
    }

    /**
     * @param array $groups
     * @return EntrantProcessor
     */
    protected function processEntrants(array $groups)
    {
        $processor = new EntrantProcessor($this->entityManager);

        foreach ($groups as $phaseGroupData) {
            $entrants = $this->smashgg->getPhaseGroupEntrants($phaseGroupData['id']);

            foreach ($entrants as $entrantData) {
                $processor->processNew($entrantData, $this->playerProcessor);
            }
        }

        return $processor;
    }
}
