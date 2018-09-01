<?php

declare(strict_types = 1);

namespace App\Command;

use App\Importer\Challonge\Importer as ChallongeImporter;
use App\Importer\Smashgg\Importer as SmashggImporter;
use App\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManagerInterface;
use Reflex\Challonge\Challonge;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class TournamentImportCommand extends ContainerAwareCommand
{
    const PROVIDER_SMASHGG = 'smash.gg';
    const PROVIDER_CHALLONGE = 'Challonge';
    const PROVIDER_TIO = 'TIO';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @var Challonge
     */
    protected $challonge;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Smashgg                $smashgg
     * @param Challonge              $challonge
     */
    public function __construct(EntityManagerInterface $entityManager, Smashgg $smashgg, Challonge $challonge)
    {
        $this->entityManager = $entityManager;
        $this->smashgg = $smashgg;
        $this->challonge = $challonge;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:tournament:import')
            ->setDescription('Import a tournament from a third-party provider')
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

        $question = new ChoiceQuestion(
            'Which provider would you like to use?',
            [ self::PROVIDER_SMASHGG, self::PROVIDER_CHALLONGE, self::PROVIDER_TIO ]
        );
        $provider = $this->io->askQuestion($question);

        if ($provider === self::PROVIDER_SMASHGG) {
            $this->executeSmashgg();
        } elseif ($provider === self::PROVIDER_CHALLONGE) {
                $this->executeChallonge();
        } else {
            $this->io->error('Unfortunately that provider is currently not supported.');

            return;
        }

        $this->io->success('The tournament was successfully imported.');
    }

    /**
     * @return void
     */
    protected function executeSmashgg()
    {
        $slug = $this->io->ask('Please enter the slug of this tournament');
        $events = $this->smashgg->getTournamentEvents($slug, true);

        $ids = [];
        $answers = [];

        foreach ($events as $event) {
            $ids[$event['name']] = $event['id'];
            $answers[] = $event['name'];
        }

        $question = new ChoiceQuestion('Please select the IDs of the events you would like to import', $answers);
        $question->setMultiselect(true);
        $selectedEvents = (array) $this->io->askQuestion($question);

        foreach ($selectedEvents as &$selectedEvent) {
            $selectedEvent = $ids[$selectedEvent];
        }

        $importer = new SmashggImporter($this->io, $this->entityManager, $this->smashgg);
        $importer->import($slug, $selectedEvents);
    }

    /**
     * @return void
     */
    protected function executeChallonge()
    {
        $slug = $this->io->ask('Please enter the slug of this tournament');

        $importer = new ChallongeImporter($this->io, $this->entityManager, $this->challonge);
        $importer->import($slug);
    }
}
