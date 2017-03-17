<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

use CoreBundle\Entity\Country;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer
{
    /**
     * @var array
     */
    protected $rounds = [
        1 => 1, // 'W1'
        2 => 2, // 'W2'
        3 => 3, // 'W3'
        4 => 4, // 'W4'
        5 => 5, // 'W5'
        6 => 6, // 'W6'
        7 => 7, // 'W7'
        8 => 8, // 'W8'
        9 => 9, // 'L1'
        10 => -1, // 'L2'
        11 => -2, // 'L3'
        12 => -3, // 'L4'
        13 => -4, // 'L5'
        14 => -5, // 'L6'
        15 => -6, // 'L7'
        16 => -7, // 'L8'
        17 => -8, // 'L9'
        18 => -9, // 'L10'
        19 => -10, // 'L11'
        20 => -11, // 'L12'
        21 => -12, // 'L13'
        22 => -13, // 'L14'
        23 => 1, // 'R1',
        24 => 2, // 'R2',
        25 => 3, // 'R3',
        26 => 4, // 'R4',
        27 => 5, // 'R5',
        28 => 6, // 'R6',
        29 => 7, // 'R7',
        30 => 8, // 'R8',
        31 => 9, // 'R9',
        32 => 10, // 'R10',
        33 => 10, // 'GF1'
        34 => 11, // 'GF2'
    ];

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var string
     */
    protected $contentDirPath;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $countries = [];

    /**
     * @var array
     */
    protected $players = [];

    /**
     * @var array
     */
    protected $tournaments = [];

    /**
     * @var array
     */
    protected $phaseGroups = [];

    /**
     * @var array
     */
    protected $entrants = [];

    /**
     * Please note that if you don't import all scenarios at once, duplicate player profiles will be created. The
     * 'disable' functionality only exists for testing purposes.
     *
     * @var array
     */
    protected $scenarios = [
        'NoPhasesMultipleEvents'       => false, // Cleared (273 tournaments)
        'NoPhasesSingleEventBracket'   => true, // Cleared (1057 tournaments)
        'NoPhasesSingleEventNoBracket' => false, // Cleared (11 tournaments)
        'PhasesMultipleEvents'         => false, // Cleared (114 tournaments)
        'PhasesSingleEventBracket'     => false, // Cleared (75 tournaments)
        'PhasesSingleEventNoBracket'   => false, // Cleared (1 tournament)
    ];

    /**
     * @param SymfonyStyle $io
     * @param string $contentDirPath
     * @param EntityManager $entityManager
     */
    public function __construct(SymfonyStyle $io, string $contentDirPath, EntityManager $entityManager)
    {
        $this->io = $io;
        $this->contentDirPath = $contentDirPath;
        $this->entityManager = $entityManager;
    }

    /**
     * At the time of writing there is a small discrepancy between the number of tournaments imported from the
     * SmashRanking import and number of tournaments after sorting the events by tournament. This happens because a
     * small number of tournaments (currently four) do not have any events attached to them.
     *
     * @return void
     */
    public function import()
    {
        $this->io->title('Importing data from the smashranking.eu database...');

        $this->io->text('Retrieving players...');
        $this->players = $this->getPlayers();
        $this->io->text(sprintf('Retrieved %s players.', count($this->players)));

        $this->io->text('Retrieving tournaments...');
        $this->tournaments = $this->getTournaments();
        $this->io->text(sprintf('Retrieved %s tournaments.', count($this->tournaments)));

//        foreach ($this->scenarios as $scenario => $active) {
//            if (!$active) {
//                continue;
//            }
//
//            $this->io->section("Importing tournaments for scenario '{$scenario}'...");
//            $class = 'AppBundle\Importer\SmashRanking\\'.$scenario;
//
//            /** @var AbstractScenario $scenario */
//            $scenario = new $class($this, $this->io, $this->entityManager);
//            $scenario->importWithConfiguration();
//        }
//
//        $this->io->newLine();
//        $this->io->text(sprintf('Created %d phase groups...', count($this->phaseGroups)));
//
//        $this->io->text('Processing matches...');
//        $this->processMatches();

        $this->io->text('Flushing entity manager...');
        $this->entityManager->flush();

        $this->io->success('Successfully imported the data from smashranking.eu!');
    }

    /**
     * @param string $contentKey
     * @return array
     */
    public function getContentFromJson(string $contentKey)
    {
        $jsonPath = realpath($this->contentDirPath."/ranking.{$contentKey}.json");
        $json = file_get_contents($jsonPath);

        if (!$json) {
            throw new \InvalidArgumentException("No JSON file found for key {$contentKey}.");
        }

        return \GuzzleHttp\json_decode($json, true);
    }

    /**
     * @return array
     *
     * @TODO Gather more data about the players from the SmashRanking database export.
     */
    public function getPlayers()
    {
        if (count($this->players) > 0) {
            return $this->players;
        }

        $players = $this->getContentFromJson('smasher');

        foreach ($players as $playerId => &$player) {
            $country = $this->getCountryBySmashRankingId($player['country']);
            $nationality = $this->getCountryBySmashRankingId($player['nationality']);

            if ($nationality === null && $country instanceof Country) {
                $nationality = $country;
            }

            $entity = new Player();
            $entity->setOriginalId($playerId);
            $entity->setName($player['name'] ? $player['name'] : null);
            $entity->setGamerTag($player['tag']);
            $entity->setNationality($nationality);
            $entity->setCountry($country);
            $entity->setRegion($player['region']);
            $entity->setCity($player['city']);
            $entity->setIsCompeting($player['active']);

            $this->entityManager->persist($entity);
            $player = $entity;
        }

        return $players;
    }

    /**
     * @param int $playerId
     * @return Player
     */
    public function getPlayerById($playerId)
    {
        if (!array_key_exists($playerId, $this->players)) {
            throw new \InvalidArgumentException("Player #{$playerId} could not be found.");
        }

        $player = $this->players[$playerId];

        Assert::isInstanceOf($player, 'CoreBundle\Entity\Player');

        return $player;
    }

    /**
     * @return array
     *
     * @TODO Gather more data about the tournaments from the SmashRanking database export.
     */
    public function getTournaments()
    {
        if (count($this->tournaments) > 0) {
            return $this->tournaments;
        }

        $tournaments = $this->getContentFromJson('tournament');

        foreach ($tournaments as $tournamentId => &$tournament) {
            $country = $this->getCountryBySmashRankingId($tournament['country']);

            $entity = new Tournament();
            $entity->setOriginalId($tournamentId);
            $entity->setName($tournament['name']);
            $entity->setCountry($country);
            $entity->setResultsPage($tournament['result_page']);
            $entity->setIsComplete(true);
            $entity->setIsActive(true);

            $this->entityManager->persist($entity);
            $tournament = $entity;
        }

        return $tournaments;
    }

    /**
     * @param int $tournamentId
     * @return Tournament
     */
    public function getTournamentById($tournamentId)
    {
        if (!array_key_exists($tournamentId, $this->tournaments)) {
            throw new \InvalidArgumentException("Tournament #{$tournamentId} could not be found.");
        }

        $tournament = $this->tournaments[$tournamentId];

        Assert::isInstanceOf($tournament, 'CoreBundle\Entity\Tournament');

        return $tournament;
    }

    /**
     * @param int        $originalEventId
     * @param PhaseGroup $phaseGroup
     */
    public function addPhaseGroup($originalEventId, PhaseGroup $phaseGroup)
    {
        $this->phaseGroups[$originalEventId] = $phaseGroup;
    }

    /**
     * @param int $id
     * @return Country|null
     */
    protected function getCountryBySmashRankingId($id)
    {
        if (count($this->countries) === 0 ) {
            $this->countries = $this->getContentFromJson('country');
        }

        foreach ($this->countries as $countryId => $country) {
            if ($countryId == $id) {
                return $this->entityManager->getRepository('CoreBundle:Country')->findOneBy([
                    'code' => $country['short'],
                ]);
            }
        }

        return null;
    }

    /**
     * @return void
     */
    protected function processMatches()
    {
        $matches = $this->getContentFromJson('match');
        $counter = 0;

        foreach ($matches as $matchId => $match) {
            $eventId = $match['event'];

            if (!array_key_exists($eventId, $this->phaseGroups)) {
                continue;
            }

            /** @var PhaseGroup $phaseGroup */
            $phaseGroup = $this->phaseGroups[$eventId];
            $event = $phaseGroup->getPhase()->getEvent();
            $tournament = $event->getTournament();
            $tournamentId = array_search($tournament, $this->tournaments);

            $entrantOne = $this->getEntrant($match['winner'], $tournamentId);
            $entrantTwo = $this->getEntrant($match['loser'], $tournamentId);
            $round = $match['round'];

            if ($round === null) {
                $round = 1;
            }

            if ($round > 8 && $round < 23 && $phaseGroup->getType() === PhaseGroup::TYPE_SINGLE_ELIMINATION) {
                // If the round goes above 8 it means we have a match in the losers bracket, therefore this is a double
                // elimination bracket.
                $phaseGroup->setType(PhaseGroup::TYPE_DOUBLE_ELIMINATION);
            }

            $round = $this->rounds[$round];

            $set = new Set();
            $set->setOriginalId($matchId);
            $set->setPhaseGroup($phaseGroup);
            $set->setRound($round);
            $set->setEntrantOne($entrantOne);
            $set->setEntrantTwo($entrantTwo);
            $set->setWinner($entrantOne);
            $set->setWinnerScore($match['games_winner']);
            $set->setLoser($entrantTwo);
            $set->setLoserScore($match['games_loser']);
            $set->setIsForfeit($match['forfeit']);
            $set->setIsRanked($match['publish']);

            $this->entityManager->persist($set);

            $counter++;
        }

        $this->io->text("Counted {$counter} matches (sets).");
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

        $player = $this->getPlayerById($playerId);

        $entrant = new Entrant();
        $entrant->setName($player->getGamerTag());
        $entrant->addPlayer($player);
        $player->addEntrant($entrant);

        $this->entityManager->persist($entrant);

        $this->entrants[$tournamentId][$playerId] = $entrant;

        return $entrant;
    }
}