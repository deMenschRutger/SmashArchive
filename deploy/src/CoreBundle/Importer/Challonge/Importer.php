<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Challonge;

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
     * @param string $challongeId
     */
    public function import($challongeId)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $this->io->writeln('Processing entrants...');
        $entrantProcessor = new EntrantProcessor($this->entityManager);
        $entrants = $this->challonge->getParticipants($challongeId);

        foreach ($entrants as $entrant) {
            $entrantProcessor->processNew($entrant);
        }

        $this->io->writeln('Processing sets...');
        $setProcessor = new SetProcessor($this->entityManager);
        $sets = $this->challonge->getMatches($challongeId);

        foreach ($sets as $set) {
            $setProcessor->processNew($set, $entrantProcessor);
        }

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();
    }
}
