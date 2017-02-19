<?php

declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Importer\SmashRanking\Importer;
use CoreBundle\Entity\Tournament;
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
     * @var Importer
     */
    protected $importer;

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
                'split-models',
                's',
                InputOption::VALUE_OPTIONAL,
                'Split the export of the smashranking.eu database into smaller JSON files based on model name.'
            )
            ->addOption(
                'count-model-items',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Count the number of items for the specified model.'
            )
            ->addOption(
                'import',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Import the smashranking.eu database export into the database.'
            )
            ->addOption(
                'match-smashgg-ids',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Match the imported data from SmashRanking to the same data on smash.gg and store the IDs.'
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
        $this->importer = new Importer($this->io, $this->getContentDirPath(), $this->entityManager);

        $modelName = $input->getOption('count-model-items');

        if ($input->getOption('split-models')) {
            $this->splitModels();
        } elseif($modelName) {
            $this->countModels($modelName);
        } elseif($input->getOption('import')) {
            $this->importer->import();
        } elseif($input->getOption('match-smashgg-ids')) {
            $this->matchSmashggIds();
        }
    }

    /**
     * This method will turn the entire export of the SmashRanking database into more manageable chunks.
     *
     * @return void
     */
    protected function splitModels()
    {
        $contentDirPath = $this->getContentDirPath();
        $jsonPath = realpath($contentDirPath.'/db.json');
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
            $filePath = $contentDirPath."/{$key}.json";

            file_put_contents($filePath, $contents);
        }
    }

    /**
     * @param string $modelName
     *
     * Counts so far:
     *
     * smasher: 7893
     * tournament: 1535
     * event: 3244
     * match: 94529
     */
    protected function countModels(string $modelName)
    {
        $count = count($this->importer->getContentFromJson($modelName));

        $this->io->text(sprintf("Counted %d items for model name '%s'.", $count, $modelName));
    }

    /**
     * @TODO Compare smash.gg IDs with SmashRanking IDs.
     */
    protected function matchSmashggIds()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $smashggTournaments = $queryBuilder
            ->select('t.name')
            ->from('CoreBundle:Tournament', 't')
            ->leftJoin('t.events', 'e')
            ->leftJoin('e.phases', 'p')
            ->leftJoin('p.phaseGroups', 'pg')
            ->where("t.resultsPage LIKE '%smash.gg%'")
            ->orWhere("pg.resultsUrl LIKE '%smash.gg%'")
            ->getQuery()
            ->getResult()
        ;
        $smashggTournament = current($smashggTournaments);
    }

    /**
     * @return string|bool
     */
    protected function getContentDirPath()
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();

        return realpath($rootDir.'/../var/tmp/smashranking/');
    }
}
