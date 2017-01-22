<?php

declare(strict_types=1);

namespace AppBundle\Command;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Set;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventGenerateResultsCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('event:generate:results')
            ->setDescription('Generate the complete results for an entire event.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     *
     * @TODO Take into account multiple phases and phase groups.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $eventId = 1;

        /** @var Phase[] $phases */
        $phases = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from('CoreBundle:Phase', 'p')
            ->join('p.event', 'e')
            ->where('e.id = ?1')
            ->setParameter(1, $eventId)
            ->orderBy('p.phaseOrder')
            ->getQuery()
            ->getResult()
        ;

        $sets = [];

        foreach ($phases as $phase) {
            /** @var PhaseGroup $phaseGroup */
            foreach ($phase->getPhaseGroups() as $phaseGroup) {
                /** @var Set $set */
                foreach ($phaseGroup->getSets() as $set) {
                    $round = $set->getRound();

                    if ($round < 0) {
                        $sets[$round][] = $set;
                    }
                }
            }
        }

        $counter = count($sets[-1]) * 2 + 1;

        foreach ($sets as $round => $roundSets) {
            if ($round < 0) {
                foreach ($roundSets as $set) {
                    $counter--;
                    $loser = $set->getLoser();

                    if ($loser instanceof Entrant) {
                        $this->io->writeln($counter.': '.$loser->getName());
                    } else {
                        $this->io->writeln('bye');
                    }
                }
            }
        }

        // TODO Include GFs.
    }
}
