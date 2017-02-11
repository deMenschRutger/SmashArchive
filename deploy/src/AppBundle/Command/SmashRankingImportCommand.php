<?php

declare(strict_types=1);

namespace AppBundle\Command;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
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
     * @var array
     */
    protected $entrants;

    /**
     * @var array
     */
    protected $players;

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
     * @return void
     */
    protected function categorizeModels()
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $jsonPath = realpath($rootDir.'/../var/tmp/db.json');
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
            $dirPath = realpath($rootDir.'/../var/tmp/');
            $filePath = $dirPath."/sreu.{$key}.json";

            file_put_contents($filePath, $contents);
        }
    }

    /**
     *
     */
    protected function import()
    {
        /*
        eventtypes are TYPE_CHOICES = (
        (1, u'Swiss System'),
        (2, u'Round Robin Pool'),
        (3, u'Bracket Pool'),
        (4, u'Bracket'),
        (5, u'Intermediate Bracket'),
        (6, u'Amateur Bracket'),
        */




    /*
    match rounds are ROUND_CHOICES = (
        (1, u'W1'),
        (2, u'W2'),
        (3, u'W3'),
        (4, u'W4'),
        (5, u'W5'),
        (6, u'W6'),
        (7, u'W7'),
        (8, u'W8'),
        (9, u'L1'),
        (10, u'L2'),
        (11, u'L3'),
        (12, u'L4'),
        (13, u'L5'),
        (14, u'L6'),
        (15, u'L7'),
        (16, u'L8'),
        (17, u'L9'),
        (18, u'L10'),
        (19, u'L11'),
        (20, u'L12'),
        (21, u'L13'),
        (22, u'L14'),
        (23, u'R1'),
        (24, u'R2'),
        (25, u'R3'),
        (26, u'R4'),
        (27, u'R5'),
        (28, u'R6'),
        (29, u'R7'),
        (30, u'R8'),
        (31, u'R9'),
        (32, u'R10'),
        (33, u'GF1'),
        (34, u'GF2'),
    )
    */


//        $this->io->title('Import data from the smashranking.eu database...');




        /*
         * 1 Tournament has phases or no phases?
         * 1.1 Phases -> Can be imported (count: 190)
         * 1.2 No Phases -> 2 (count: 1341)
         *
         * 2 No phases: Single event or multiple events?
         * 2.1 Single event -> 3 (count: 1068)
         * 2.2 Multiple events -> 4 (count: 273)
         *
         * 3 Single event: is it a bracket (type 4, 5 or 6)?
         * 3.1 Yes -> Import them all (count: 1057)
         * 3.2 No -> Possible, but placings need to be determined in a different way (count: 11)
         *
         * 4 Multiple events: are they all brackets (type 4, 5 or 6)?
         * 4.1 Yes -> They must be separate events somehow (maybe singles and doubles)?
         * 4.2 No -> We probably have phases that weren't marked as phases.
         */



        $tournaments = $this->getFromJson('tournament');

        foreach ($tournaments as $tournamentId => &$tournament) {
            $entity = new Tournament();
            $entity->setName($tournament['name']);

            $this->entityManager->persist($entity);

            $tournament = $entity;
        }



//        $smashggIds = $this->getSmashggIds();
//
//        $events = array_filter($this->getFromJson('event'), function ($event) use ($smashggIds) {
//            return in_array($event['tournament'], $smashggIds);
//        });



        $events = $this->getFromJson('event');
        $eventsPerTournament = [];

        foreach ($events as $eventId => $event) {
            $tournamentId = $event['tournament'];

            if (!array_key_exists($tournamentId, $eventsPerTournament)) {
                $eventsPerTournament[$tournamentId] = [
                    'events' => [],
                    'hasPhases' => false,
                ];
            }

            $eventsPerTournament[$tournamentId]['events'][$eventId] = $event;

            if ($event['phase'] !== null) {
                $eventsPerTournament[$tournamentId]['hasPhases'] = true;
            }
        }

        $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) {
            return !$tournament['hasPhases'];
        });

        $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) {
            if (count($tournament['events']) > 1) {
                return false;
            }

            foreach ($tournament['events'] as $eventId => $event) {
                if (!in_array($event['type'], [4, 5, 6])) {
                    return false;
                }
            }

            return true;
        });

        $melee = $this->entityManager->find('CoreBundle:Game', 1);
        $phaseGroups = [];

        foreach ($eventsPerTournament as $tournamentId => $tournament) {
            $tournamentEntity = $tournaments[$tournamentId];

            foreach ($tournament['events'] as $eventId => $event) {
                $entity = new Event();
                $entity->setName('Melee Singles');
                $entity->setGame($melee);
                $entity->setTournament($tournamentEntity);

                $this->entityManager->persist($entity);

                $phase = new Phase();
                // TODO Change the name based on event type 4, 5 or 6.
                $phase->setName('Bracket');
                $phase->setEvent($entity);
                $phase->setPhaseOrder(1);

                $this->entityManager->persist($phase);

                $phaseGroup = new PhaseGroup();
                $phaseGroup->setName('Bracket');
                // TODO Make the type an entity?
                $phaseGroup->setType(2);
                $phaseGroup->setPhase($phase);

                $this->entityManager->persist($phaseGroup);

                $phaseGroups[$eventId] = $phaseGroup;
            }
        }

        $this->entityManager->flush();



        $matches = $this->getFromJson('match');
        $this->players = $this->getFromJson('smasher');
        $counter = 0;

        foreach ($matches as $matchId => $match) {
            $eventId = $match['event'];

            if (!array_key_exists($eventId, $phaseGroups)) {
                continue;
            }

            /** @var PhaseGroup $phaseGroup */
            $phaseGroup = $phaseGroups[$eventId];
            $tournamentId = $phaseGroup->getPhase()->getEvent()->getTournament()->getId();
            $entrantOne = $this->getEntrant($match['winner'], $tournamentId);
            $entrantTwo = $this->getEntrant($match['loser'], $tournamentId);

            $set = new Set();
            $set->setPhaseGroup($phaseGroup);
            // TODO Map rounds to the way smash.gg uses them.
            $set->setRound($match['round']);
            $set->setEntrantOne($entrantOne);
            $set->setEntrantTwo($entrantTwo);
            $set->setWinner($entrantOne);
            $set->setLoser($entrantTwo);

            $this->entityManager->persist($set);

            $counter++;
        }

        $this->io->comment("Counted {$counter} sets.");

        $this->entityManager->flush();



//        $this->io->success('Successfully imported the data!');
    }

    /**
     * @param integer $playerId
     * @param integer $tournamentId
     * @return Entrant|bool
     */
    protected function getEntrant($playerId, $tournamentId)
    {
        if (isset($this->entrants[$tournamentId][$playerId])) {
            return $this->entrants[$tournamentId][$playerId];
        }

        if (!array_key_exists($playerId, $this->players)) {
            return false;
        }

        if (array_key_exists('entity', $this->players[$playerId])) {
            $player = $this->players[$playerId]['entity'];
        } else {
            $tag = $this->players[$playerId]['tag'];
            $player = new Player();
            $player->setGamerTag($tag);

            $this->entityManager->persist($player);

            $this->players[$playerId]['entity'] = $player;
        }

        $entrant = new Entrant();
        $entrant->setName($player->getGamerTag());
        $entrant->addPlayer($player);
        $player->addEntrant($entrant);

        $this->entityManager->persist($entrant);

        $this->entrants[$tournamentId][$playerId] = $entrant;

        return $entrant;
    }

    /**
     * @return array
     */
    protected function getSmashggIds()
    {
//        $this->io->title('Import smash.gg data from the smashranking.eu database...');

        $tournaments = array_filter($this->getFromJson('tournament'), function ($tournament) {
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

//        $this->io->success('Successfully imported the data!');

        return array_keys($tournaments);
    }

    /**
     * @param string $key
     * @return array
     */
    protected function getFromJson(string $key)
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $jsonPath = realpath($rootDir."/../var/tmp/sreu.ranking.{$key}.json");
        $json = file_get_contents($jsonPath);

        if (!$json) {
            throw new \InvalidArgumentException("No JSON file found for key {$key}.");
        }

        return \GuzzleHttp\json_decode($json, true);
    }
}
