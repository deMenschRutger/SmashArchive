<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Result;
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
     * @var bool
     */
    protected $verbose;

    /**
     * @var array
     */
    protected $results = [];

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->verbose = $input->getOption('verbose');

        $events = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('e')
            ->from('CoreBundle:Event', 'e')
            ->getQuery()
            ->getResult()
        ;

        if (!$this->verbose) {
            $this->io->progressStart(count($events));
        }

        /** @var Event $event */
        foreach ($events as $event) {
            $this->processEvent($event->getId());

            if (!$this->verbose) {
                $this->io->progressAdvance(1);
            }

            $this->results = [];
        }

        if (!$this->verbose) {
            $this->io->progressFinish();
        }

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();
        $this->io->writeln('The results were succesfully imported.');
    }

    /**
     * @param int $eventId
     */
    protected function processEvent(int $eventId)
    {
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

        $startRank = 0;

        foreach ($phases as $phase) {
            $phaseGroupEntrantCount = 0;

            // TODO Remove this once pool phases can be properly processed.
            if (count($phase->getPhaseGroups()) > 1) {
                return;
            }

            /** @var PhaseGroup $phaseGroup */
            foreach ($phase->getPhaseGroups() as $phaseGroup) {
                $phaseGroupEntrantCount += $this->processPhaseGroup($phaseGroup, $startRank);
            }

            $startRank += $phaseGroupEntrantCount;
        }
    }

    /**
     * @param PhaseGroup $phaseGroup
     * @param int        $rank
     * @return int The number of entrants in the phase group.
     */
    protected function processPhaseGroup(PhaseGroup $phaseGroup, $rank)
    {
        $event = $phaseGroup->getPhase()->getEvent();
        $sets = $phaseGroup->getSets()->getValues();

        if (count($sets) === 0) {
            if ($this->verbose) {
                $this->io->writeln('No sets found.');
            }

            return 0;
        }

        $setsByRound = [];
        $totalResults = 0;

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
            $totalResults += 1;
            $rank += 1;
            $this->addResult($event, $grandFinalsWinner, $rank);
        }

        if ($grandFinalsLoser instanceof Entrant) {
            $totalResults += 1;
            $rank += 1;
            $this->addResult($event, $grandFinalsLoser, $rank);
        }

        $rank += 1;

        foreach ($setsByRound as $round => $roundSets) {
            $roundSetCount = 0;

            foreach ($roundSets as $set) {
                $loser = $set->getLoser();

                if ($loser instanceof Entrant) {
                    $totalResults += 1;
                    $roundSetCount += 1;
                    $this->addResult($event, $loser, $rank);
                }
            }

            $rank += $roundSetCount;
        }

        return $totalResults;
    }

    /**
     * @param Event   $event
     * @param Entrant $entrant
     * @param int     $rank
     */
    protected function addResult(Event $event, Entrant $entrant, int $rank)
    {
        $resultId = $event->getId().'-'.$entrant->getId();

        if (array_key_exists($resultId, $this->results)) {
            /** @var Result $result */
            $result = $this->results[$resultId];

            if ($result->getRank() <= $rank) {
                return;
            }
        }

        $result = new Result();
        $result->setEntrant($entrant);
        $result->setEvent($event);
        $result->setRank($rank);

        $this->entityManager->persist($result);
        $this->results[$resultId] = $result;

        if ($this->verbose) {
            $this->io->writeln($rank.': '.$entrant->getName());
        }
    }
}
