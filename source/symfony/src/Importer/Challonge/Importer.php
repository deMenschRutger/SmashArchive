<?php

declare(strict_types = 1);

namespace App\Importer\Challonge;

use App\Entity\Phase;
use App\Entity\PhaseGroup;
use App\Entity\Tournament;
use App\Importer\AbstractImporter;
use App\Importer\Challonge\Processor\EntrantProcessor;
use App\Importer\Challonge\Processor\SetProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Reflex\Challonge\Challonge;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer extends AbstractImporter
{
    /**
     * @var Challonge
     */
    protected $challonge;

    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @var EntrantProcessor
     */
    protected $entrantProcessor;

    /**
     * @param SymfonyStyle           $io
     * @param EntityManagerInterface $entityManager
     * @param Challonge              $challonge
     */
    public function __construct(SymfonyStyle $io, EntityManagerInterface $entityManager, Challonge $challonge)
    {
        $this->setIo($io);
        $this->setEntityManager($entityManager);
        $this->challonge = $challonge;
    }

    /**
     * @param string $slug
     */
    public function import($slug)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $this->io->writeln('Retrieving tournament...');

        // TODO Improve the query (load more entities).
        $this->tournament = $this->getRepository('App:Tournament')->findOneBy([
            'slug' => $slug,
        ]);

        if (!$this->tournament instanceof Tournament) {
            throw new \InvalidArgumentException('The tournament could not be found.');
        }

        if ($this->tournament->getSource() !== Tournament::SOURCE_CHALLONGE) {
            throw new \InvalidArgumentException('The tournament does not have Challonge as its source.');
        }

        $this->entrantProcessor = new EntrantProcessor($this->entityManager);

        foreach ($this->tournament->getEvents() as $event) {
            foreach ($event->getPhases() as $phase) {
                /** @var Phase $phase */
                foreach ($phase->getPhaseGroups() as $phaseGroup) {
                    $this->processPhaseGroup($phaseGroup);
                }
            }
        }

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();
    }

    /**
     * @param PhaseGroup $phaseGroup
     */
    protected function processPhaseGroup(PhaseGroup $phaseGroup)
    {
        $challongeId = $phaseGroup->getExternalId();

        $this->io->writeln('Processing entrants...');
        $entrants = $this->challonge->getParticipants($challongeId);

        foreach ($entrants as $entrant) {
            $this->entrantProcessor->processNew($entrant, $phaseGroup->getPhase());
        }

        $this->io->writeln('Processing sets...');
        $setProcessor = new SetProcessor($this->entityManager);
        $sets = $this->challonge->getMatches($challongeId);

        foreach ($sets as $set) {
            $setProcessor->processNew($set, $this->entrantProcessor, $phaseGroup);
        }
    }
}
