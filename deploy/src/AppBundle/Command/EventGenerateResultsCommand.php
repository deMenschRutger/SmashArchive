<?php

declare(strict_types=1);

namespace AppBundle\Command;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Set;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\ArrayHydrator;
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
            ->setName('app:event:generate:results')
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

        $eventId = 3;

        /** @var Phase[] $phases */
        $phases = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p, pg, s')
            ->from('CoreBundle:Phase', 'p')
            ->join('p.phaseGroups', 'pg')
            ->join('pg.sets', 's')
            ->join('p.event', 'e')
            ->where('e.id = ?1')
            ->setParameter(1, $eventId)
            ->orderBy('p.phaseOrder', 'DESC')
            ->addOrderBy('s.round')
            ->getQuery()
            ->getResult()
        ;

        foreach ($phases as $phase) {
            /** @var PhaseGroup $phaseGroup */
            foreach ($phase->getPhaseGroups() as $phaseGroup) {
                $this->processPhaseGroup($phaseGroup);
            }
        }
    }

    /**
     * @param PhaseGroup $phaseGroup
     */
    protected function processPhaseGroup(PhaseGroup $phaseGroup)
    {
        $sets = $phaseGroup->getSets()->getValues();

        if (count($sets) === 0) {
            $this->io->writeln('No sets found.');

            return;
        }

        $setsByRound = [];

        /** @var Set $set */
        foreach ($sets as $set) {
            $round = $set->getRound();

            if ($round < 0) {
                $setsByRound[$round][] = $set;
            }
        }

        /** @var Set $grandFinals */
        $grandFinals = array_pop($sets);

        if (!$grandFinals->getWinner() instanceof Entrant) {
            $grandFinals = array_pop($sets);
        }

        $grandFinalsWinner = $grandFinals->getWinner();
        $grandFinalsLoser = $grandFinals->getLoser();

        if ($grandFinalsWinner instanceof Entrant) {
            $this->io->writeln('1: '.$grandFinals->getWinner()->getName());
        }

        if ($grandFinalsLoser instanceof Entrant) {
            $this->io->writeln('2: '.$grandFinals->getLoser()->getName());
        }

        $ranking = 3;

        foreach ($setsByRound as $round => $roundSets) {
            foreach ($roundSets as $set) {
                $loser = $set->getLoser();

                if ($loser instanceof Entrant) {
                    $this->io->writeln($ranking.': '.$loser->getName());
                }
            }

            $ranking += count($roundSets);
        }
    }
}
