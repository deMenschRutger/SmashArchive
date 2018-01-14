<?php

declare(strict_types = 1);

namespace AppBundle\Command;

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
class EntrantOriginPhaseCompleteCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

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
            ->setName('app:entrant:origin-phase:complete')
            ->setDescription('Add missing origin phases to entrants.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entrants = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('en.id')
            ->from('CoreBundle:Entrant', 'en')
            ->leftJoin('en.originPhase', 'op')
            ->where('en.originPhase IS NULL')
            ->getQuery()
            ->getResult()
        ;

        $setRepository = $this->entityManager->getRepository('CoreBundle:Set');

        $io->progressStart(count($entrants));

        foreach ($entrants as $entrant) {
            $entrantId = $entrant['id'];
            $sets = $setRepository->findByEntrantId($entrantId);

            if (count($sets) === 0) {
                continue;
            }

            /** @var Set $set */
            $set = current($sets);
            $phaseGroup = $set->getPhaseGroup();

            if (!$phaseGroup instanceof PhaseGroup) {
                continue;
            }

            $originPhase = $phaseGroup->getPhase();

            $this
                ->entityManager
                ->createQueryBuilder()
                ->update('CoreBundle:Entrant', 'en')
                ->set('en.originPhase', ':originPhase')
                ->where('en.id = :id')
                ->setParameter('originPhase', $originPhase)
                ->setParameter('id', $entrantId)
                ->getQuery()
                ->execute()
            ;

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('The origin phases were successfully completed!');
    }
}
