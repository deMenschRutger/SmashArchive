<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg;

use CoreBundle\Entity\Country;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractImporter;
use CoreBundle\Importer\Smashgg\Processor\EntrantProcessor;
use CoreBundle\Importer\Smashgg\Processor\EventProcessor;
use CoreBundle\Importer\Smashgg\Processor\GameProcessor;
use CoreBundle\Importer\Smashgg\Processor\PhaseGroupProcessor;
use CoreBundle\Importer\Smashgg\Processor\PhaseProcessor;
use CoreBundle\Importer\Smashgg\Processor\PlayerProcessor;
use CoreBundle\Importer\Smashgg\Processor\SetProcessor;
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
     * Example slugs:
     *
     * 'arcamelee-1'
     * 'dgsummer-clash-1'
     * 'garelaf-x'
     * 'genesis-4'
     * 'syndicate-2016'
     *
     * @param string $smashggId
     * @param array  $eventIds
     * @return Tournament
     */
    public function import($smashggId, $eventIds)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $this->io->writeln('Retrieving tournament...');
        $this->tournament = $this->getTournament($smashggId);

        if ($this->tournament->getSource() !== Tournament::SOURCE_SMASHGG) {
            throw new \InvalidArgumentException('The tournament does not have smash.gg as its source.');
        }

        $this->io->writeln('Processing games...');
        $this->gameProcessor = $this->processGames();

        $this->io->writeln('Processing events...');
        $this->eventProcessor = $this->processEvents($eventIds);

        $this->io->writeln('Processing phases...');
        $this->phaseProcessor = $this->processPhases();

        $phaseIds = array_keys($this->phaseProcessor->getAllPhases());
        $groups = $this->smashgg->getTournamentGroups($this->tournament->getExternalId(), $phaseIds);

        $this->phaseGroupProcessor = $this->processPhaseGroups($groups);
        $this->playerProcessor = $this->processPlayers($groups);

        $this->io->writeln('Processing entrants...');
        $this->entrantProcessor = $this->processEntrants($groups);

        $this->io->writeln('Processing sets...');
        $this->entrantProcessor = $this->processSets($groups);

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();

        $this->io->writeln('Counting confirmed players for the tournament...');
        $this->entityManager->clear();

        $this->tournament = $this->getRepository('CoreBundle:Tournament')->find($this->tournament->getId());
        $this->tournament->setPlayerCount();

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();

        return $this->tournament;
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    protected function getTournament($slug)
    {
        $smashggTournament = $this->smashgg->getTournament($slug);
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'externalId' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSource(Tournament::SOURCE_SMASHGG);
            $tournament->setExternalId($slug);
            $tournament->setIsActive(true);
            $tournament->setIsComplete(true);

            $this->entityManager->persist($tournament);
        }

        $dateStart = new \DateTime();
        $dateStart->setTimestamp($smashggTournament['startAt']);
        $country = $this->findCountry(null, $smashggTournament['countryCode']);

        $tournament->setName($smashggTournament['name']);
        $tournament->setCity($smashggTournament['city']);
        $tournament->setDateStart($dateStart);

        if ($country) {
            $tournament->setCountry($country);
        }

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
        $games = $this->smashgg->getTournamentVideogames($this->tournament->getExternalId(), true);
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
        $events = $this->smashgg->getTournamentEvents($this->tournament->getExternalId(), true);
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
        $phases = $this->smashgg->getTournamentPhases($this->tournament->getExternalId());
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
                $processor->processNew($playerData, $country, $this->tournament);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->newLine();

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
            $event = null;
            $phaseGroup = $this->phaseGroupProcessor->findPhaseGroup($phaseGroupData['id']);

            if ($phaseGroup instanceof PhaseGroup) {
                $event = $phaseGroup->getPhase()->getEvent();
            }

            $entrants = $this->smashgg->getPhaseGroupEntrants($phaseGroupData['id']);

            foreach ($entrants as $entrantData) {
                $processor->processNew($entrantData, $this->playerProcessor, $event);
            }
        }

        return $processor;
    }

    /**
     * @param array $groups
     * @return SetProcessor
     */
    protected function processSets(array $groups)
    {
        $processor = new SetProcessor($this->entityManager);

        foreach ($groups as $phaseGroupData) {
            $sets = $this->smashgg->getPhaseGroupSets($phaseGroupData['id']);
            $phaseGroup = $this->phaseGroupProcessor->findPhaseGroup($phaseGroupData['id']);

            foreach ($sets as $setData) {
                $processor->processNew($setData, $this->entrantProcessor, $phaseGroup);
            }
        }

        $processor->cleanUp($this->tournament);

        return $processor;
    }
}
