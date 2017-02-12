<?php

declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Importer\SmashRanking\AbstractScenario;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 */
class SmashRankingImportCommand extends ContainerAwareCommand
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
            ->setName('app:smashranking:import')
            ->setDescription('Import data from the smashranking.eu database.')
            ->addOption(
                'categorize-models',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Split the export of the smashranking.eu database into smaller JSON files.'
            )
            ->addOption(
                'import',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Import the smashranking.eu database export into the database.'
            )
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

        if ($input->getOption('categorize-models')) {
            $this->categorizeModels();
        } elseif($input->getOption('import')) {
            $this->import();
        }
    }

    /**
     * This method will turn the entire export of the SmashRanking database into more manageable chunks.
     *
     * @return void
     */
    protected function categorizeModels()
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $jsonPath = realpath($rootDir.'/../var/tmp/smashranking/db.json');
        $json = file_get_contents($jsonPath);
        $rows = \GuzzleHttp\json_decode($json, true);

        $models = [];

        foreach ($rows as $row) {
            $model = $row['model'];
            $id = $row['pk'];

            if (!array_key_exists($model, $models)) {
                $models[$model] = [];
            }

            $models[$model][$id] = $row['fields'];
        }

        foreach ($models as $key => $rows) {
            ksort($rows);

            $contents = \GuzzleHttp\json_encode($rows, JSON_PRETTY_PRINT);
            $dirPath = realpath($rootDir.'/../var/tmp/smashranking');
            $filePath = $dirPath."/{$key}.json";

            file_put_contents($filePath, $contents);
        }
    }

    /**
     * @return void
     */
    protected function import()
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $contentDirPath = realpath($rootDir.'/../var/tmp/smashranking/');

        $this->io->title('Importing data from the smashranking.eu database...');

        // Please note that if you don't import all scenarios at once, duplicate player profiles will be created. The
        // 'disable' functionality only exists for testing purposes.
        $scenarios = [
            'NoPhasesMultipleEvents'       => false, // Cleared (273 tournaments)
            'NoPhasesSingleEventBracket'   => true,  // Cleared (1057 tournaments)
            'NoPhasesSingleEventNoBracket' => false, // Cleared (11 tournaments)
            'PhasesMultipleEvents'         => false, // Cleared (114 tournaments)
            'PhasesSingleEventBracket'     => true,  // Cleared (75 tournaments)
            'PhasesSingleEventNoBracket'   => false, // Cleared (1 tournament)
        ];
        $players = [];

        foreach ($scenarios as $scenario => $active) {
            if (!$active) {
                continue;
            }

            $this->io->section("Importing tournaments for scenario '{$scenario}'...");
            $class = 'AppBundle\Importer\SmashRanking\\'.$scenario;

            /** @var AbstractScenario $scenario */
            $scenario = new $class($contentDirPath, $this->io, $this->entityManager, $players);
            $scenario->importWithConfiguration();
            $players = $scenario->getPlayers();
        }

        $this->io->success('Successfully imported the data from smashranking.eu!');
    }

    /**
     * @return array
     * @deprecated
     */
    protected function getSmashggIds()
    {
        $tournaments = array_filter($this->getContentFromJson('tournament'), function ($tournament) {
            if (array_key_exists('smashgg_page', $tournament) &&
                mb_strlen($tournament['smashgg_page']) > 0
            ) {
                return true;
            }

            if (array_key_exists('result_page', $tournament) &&
                strpos($tournament['result_page'], 'smash.gg') !== false
            ) {
                return true;
            }

            return false;
        });

        return array_keys($tournaments);
    }
}
