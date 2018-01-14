<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerOriginTournamentCompleteCommand extends ContainerAwareCommand
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
            ->setName('app:player:origin-tournament:complete')
            ->setDescription('Add missing origin tournaments to players.')
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

        $players = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('pl, pp')
            ->from('CoreBundle:Player', 'pl')
            ->leftJoin('pl.originTournament', 'ot')
            ->join('pl.playerProfile', 'pp')
            ->where('pl.originTournament IS NULL')
            ->getQuery()
            ->getResult()
        ;

        $entrantRepository = $this->entityManager->getRepository('CoreBundle:Entrant');
        $setRepository = $this->entityManager->getRepository('CoreBundle:Set');

        $io->progressStart(count($players));

        /** @var Player $player */
        foreach ($players as $player) {
            $slug = $player->getPlayerProfile()->getSlug();
            $firstEntrant = $entrantRepository->findFirstByPlayerSlug($slug);

            if (!$firstEntrant instanceof Entrant) {
                continue;
            }

            $firstSet = $setRepository->findFirstByEntrant($firstEntrant);

            if (!$firstSet instanceof Set) {
                continue;
            }

            $originTournament = $firstSet->getPhaseGroup()->getPhase()->getEvent()->getTournament();

            $this
                ->entityManager
                ->createQueryBuilder()
                ->update('CoreBundle:Player', 'pl')
                ->set('pl.originTournament', ':originTournament')
                ->where('pl = :id')
                ->setParameter('originTournament', $originTournament)
                ->setParameter('id', $player)
                ->getQuery()
                ->execute()
            ;

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('The origin tournaments were successfully completed!');
    }
}
