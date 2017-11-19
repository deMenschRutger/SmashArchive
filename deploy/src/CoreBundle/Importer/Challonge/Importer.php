<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Challonge;

use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractImporter;
use CoreBundle\Importer\Challonge\Processor\EntrantProcessor;
use CoreBundle\Importer\Challonge\Processor\SetProcessor;
use Doctrine\ORM\EntityManager;
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
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     * @param Challonge     $challonge
     */
    public function __construct(SymfonyStyle $io, EntityManager $entityManager, Challonge $challonge)
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
        $this->tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
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
            $this->entrantProcessor->processNew($entrant, $phaseGroup->getPhase()->getEvent());
        }

        $this->io->writeln('Processing sets...');
        $setProcessor = new SetProcessor($this->entityManager);
        $sets = $this->challonge->getMatches($challongeId);

        foreach ($sets as $set) {
            $setProcessor->processNew($set, $this->entrantProcessor, $phaseGroup);
        }
    }
}
