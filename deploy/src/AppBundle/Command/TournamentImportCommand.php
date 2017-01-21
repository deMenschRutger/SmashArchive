<?php

namespace AppBundle\Command;

use AppBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch (rutger@rutgermensch.com)
 */
class TournamentImportCommand extends ContainerAwareCommand
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
            ->setName('tournament:import')
            ->setDescription('Import a tournament from a third-party (like smash.gg)')
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
        $this->io->title('Import tournament...');

        $client = new Client();
        $slug = 'syndicate-2016';
        $response = $client->get('https://api.smash.gg/tournament/'.$slug);
        $tournamentData = \GuzzleHttp\json_decode($response->getBody(), true);

        $tournament = $this
            ->entityManager
            ->getRepository('AppBundle:Tournament')
            ->findOneBy([
                'slug' => $slug,
            ]);
        ;

        if (!$tournament instanceof Tournament) {
            $slug = substr($tournamentData['entities']['tournament']['slug'], 11);

            $tournament = new Tournament();
            $tournament->setSlug($slug);

            $this->entityManager->persist($tournament);
        }

        $tournament->setName($tournamentData['entities']['tournament']['name']);

        $this->entityManager->flush();

        $this->io->success('Successfully imported the tournament!');
    }
}
