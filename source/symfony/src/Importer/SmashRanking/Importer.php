<?php

declare(strict_types = 1);

namespace App\Importer\SmashRanking;

use App\Entity\Character;
use App\Entity\Country;
use App\Entity\Entrant;
use App\Entity\Phase;
use App\Entity\PhaseGroup;
use App\Entity\Player;
use App\Entity\Profile;
use App\Entity\Series;
use App\Entity\Set;
use App\Entity\Tournament;
use App\Importer\AbstractImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer extends AbstractImporter
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
        9 => -1, // 'L1'
        10 => -2, // 'L2'
        11 => -3, // 'L3'
        12 => -4, // 'L4'
        13 => -5, // 'L5'
        14 => -6, // 'L6'
        15 => -7, // 'L7'
        16 => -8, // 'L8'
        17 => -9, // 'L9'
        18 => -10, // 'L10'
        19 => -11, // 'L11'
        20 => -12, // 'L12'
        21 => -13, // 'L13'
        22 => -14, // 'L14'
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
        33 => 11, // 'GF1'
        34 => 11, // 'GF2'
    ];

    /**
     * @var string
     */
    protected $contentDirPath;

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
    protected $tournamentSeries = [];

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
        'NoPhasesMultipleEvents'       => true, // Cleared (273 tournaments)
        'NoPhasesSingleEventBracket'   => true, // Cleared (1057 tournaments)
        'NoPhasesSingleEventNoBracket' => true, // Cleared (11 tournaments)
        'PhasesMultipleEvents'         => true, // Cleared (114 tournaments)
        'PhasesSingleEventBracket'     => true, // Cleared (75 tournaments)
        'PhasesSingleEventNoBracket'   => true, // Cleared (1 tournament)
    ];

    /**
     * @param SymfonyStyle           $io
     * @param string                 $contentDirPath
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SymfonyStyle $io, string $contentDirPath, EntityManagerInterface $entityManager)
    {
        $this->setIo($io);
        $this->contentDirPath = $contentDirPath;
        $this->setEntityManager($entityManager);
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

        $this->io->text('Retrieving tournament series...');
        $this->tournamentSeries = $this->getTournamentSeries();
        $this->io->text(sprintf('Retrieved %s tournament series.', count($this->tournamentSeries)));

        $this->io->text('Retrieving tournaments...');
        $this->tournaments = $this->getTournaments();
        $this->io->text(sprintf('Retrieved %s tournaments.', count($this->tournaments)));

        foreach ($this->scenarios as $scenario => $active) {
            if (!$active) {
                continue;
            }

            $this->io->section("Importing tournaments for scenario '{$scenario}'...");
            $class = 'App\Importer\SmashRanking\\'.$scenario;

            /** @var AbstractScenario $scenario */
            $scenario = new $class($this, $this->io, $this->entityManager);
            $scenario->importWithConfiguration();
        }

        $this->io->newLine();
        $this->io->text(sprintf('Created %d phase groups...', count($this->phaseGroups)));

        $this->io->text('Processing matches...');
        $this->processMatches();

        $this->io->text('Correcting missing grand finals...');
        $this->processGrandFinals();

        $this->io->text('Flushing entity manager...');
        $this->entityManager->flush();

        $this->io->text('Counting entrants per tournament...');
        $this->countPlayers();

        $this->io->text('Flushing entity manager...');
        $this->entityManager->flush();

        $this->io->success('Successfully imported the data from smashranking.eu!');
    }

    /**
     * @param string $contentKey
     *
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

            // Some players had a country suffix in smashranking.eu to uniquely identify them (for example: Adam (NL)). This code removes
            // that suffix. Uniquely identifying a player is now based on slugs.
            $tag = $player['tag'];
            preg_match('~^(.*) \(([A-Z]{2})\)$~', $tag, $tagMatches);

            if (array_key_exists(1, $tagMatches)) {
                $tag = $tagMatches[1];
            }

            $profile = new Profile();
            $profile->setName($player['name'] ? $player['name'] : null);
            $profile->setGamerTag($tag);
            $profile->setNationality($nationality);
            $profile->setCountry($country);
            $profile->setRegion($player['region'] ? $player['region'] : null);
            $profile->setCity($player['city'] ?  $player['city'] : null);
            $profile->setIsActive(!$player['hide']);

            if ($player['smashwiki']) {
                $profile->setProperty('smashwiki_url', $player['smashwiki']);
            }

            if ($player['twitter']) {
                $profile->setProperty('twitter_url', $player['twitter']);
            }

            if ($player['twitch']) {
                $profile->setProperty('twitch_url', $player['twitch']);
            }

            if ($player['youtube']) {
                $profile->setProperty('youtube_url', $player['youtube']);
            }

            if ($player['main']) {
                $character = $this->getCharacterBySmashRankingId($player['main']);

                if ($character instanceof Character) {
                    $profile->addMain($character);
                }
            }

            if ($player['secondary']) {
                $character = $this->getCharacterBySmashRankingId($player['secondary']);

                if ($character instanceof Character) {
                    $profile->addSecondary($character);
                }
            }

            $entity = new Player();
            $entity->setName($tag);
            $entity->setType(Player::SOURCE_SMASHRANKING);
            $entity->setExternalId(strval($playerId));
            $entity->setProfile($profile);

            $this->entityManager->persist($profile);
            $this->entityManager->persist($entity);

            $player = $entity;
        }

        return $players;
    }

    /**
     * @param int $playerId
     *
     * @return Player
     */
    public function getPlayerById($playerId)
    {
        if (!array_key_exists($playerId, $this->players)) {
            throw new \InvalidArgumentException("Player #{$playerId} could not be found.");
        }

        $player = $this->players[$playerId];

        Assert::isInstanceOf($player, 'App\Entity\Player');

        return $player;
    }

    /**
     * @return array
     */
    public function getTournamentSeries()
    {
        if (count($this->tournamentSeries) > 0) {
            return $this->tournamentSeries;
        }

        $tournamentSeries = $this->getContentFromJson('tournamentserie');

        foreach ($tournamentSeries as $seriesId => &$series) {
            $entity = new Series();
            $entity->setName($series['name']);

            $this->entityManager->persist($entity);
            $series = $entity;
        }

        return $tournamentSeries;
    }

    /**
     * @return array
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
            $entity->setExternalId(strval($tournamentId));
            $entity->setName($tournament['name']);
            $entity->setDateStart(new \DateTime($tournament['date']));
            $entity->setCountry($country);
            $entity->setRegion($tournament['region']);
            $entity->setCity($tournament['city']);
            $entity->setResultsPage($tournament['result_page']);
            $entity->setIsComplete(true);
            $entity->setIsActive(true);

            if ($tournament['serie'] && array_key_exists($tournament['serie'], $this->tournamentSeries)) {
                $entity->setSeries($this->tournamentSeries[$tournament['serie']]);
            }

            if ($tournament['tos'] && count($tournament['tos']) > 0) {
                foreach ($tournament['tos'] as $toId) {
                    $player = $this->getPlayerById($toId);
                    $entity->addOrganizer($player->getProfile());
                }
            }

            $this->entityManager->persist($entity);
            $tournament = $entity;
        }

        return $tournaments;
    }

    /**
     * @param int $tournamentId
     *
     * @return Tournament
     */
    public function getTournamentById($tournamentId)
    {
        if (!array_key_exists($tournamentId, $this->tournaments)) {
            throw new \InvalidArgumentException("Tournament #{$tournamentId} could not be found.");
        }

        $tournament = $this->tournaments[$tournamentId];

        Assert::isInstanceOf($tournament, 'App\Entity\Tournament');

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
     *
     * @return Country|null
     */
    protected function getCountryBySmashRankingId($id)
    {
        if (count($this->countries) === 0) {
            $this->countries = $this->getContentFromJson('country');
        }

        foreach ($this->countries as $countryId => $country) {
            if ($countryId == $id) {
                return $this->entityManager->getRepository('App:Country')->findOneBy([
                    'code' => $country['short'],
                ]);
            }
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return Character
     */
    protected function getCharacterBySmashRankingId($id)
    {
        return $this->entityManager->find('App:Character', $id);
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
            $phase = $phaseGroup->getPhase();
            $tournament = $phase->getEvent()->getTournament();
            $tournamentId = array_search($tournament, $this->tournaments);

            $entrantOne = $this->getEntrant($match['winner'], $tournamentId, $phase);
            $entrantTwo = $this->getEntrant($match['loser'], $tournamentId, $phase);
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
            $set->setExternalId(strval($matchId));
            $set->setPhaseGroup($phaseGroup);
            $set->setRound($round);
            $set->setEntrantOne($entrantOne);
            $set->setEntrantTwo($entrantTwo);
            $set->setWinner($entrantOne);
            $set->setWinnerScore($match['games_winner']);
            $set->setLoser($entrantTwo);
            $set->setLoserScore($match['games_loser']);
            $set->setIsRanked($match['publish']);

            if ($match['forfeit']) {
                $set->setStatus(Set::STATUS_FORFEITED);
            }

            $this->entityManager->persist($set);

            $counter++;
        }

        $this->io->text("Counted {$counter} matches (sets).");
    }

    /**
     * Sometimes a phase group does not contain a grand finals set, in which case we'll add it.
     *
     * @return void
     */
    protected function processGrandFinals()
    {
        /** @var PhaseGroup $phaseGroup */
        foreach ($this->phaseGroups as $phaseGroup) {
            if ($phaseGroup->getType() !== PhaseGroup::TYPE_DOUBLE_ELIMINATION) {
                continue;
            }

            $hasGrandFinals = false;

            /** @var Set $set */
            foreach ($phaseGroup->getSets() as $set) {
                if ($set->getRound() === 11) {
                    $hasGrandFinals = true;

                    break;
                }
            }

            if (!$hasGrandFinals) {
                $set = new Set();
                $set->setPhaseGroup($phaseGroup);
                $set->setRound(11);
                $set->setIsRanked(false);
                $set->setStatus(Set::STATUS_NOT_PLAYED);

                $this->entityManager->persist($set);
            }
        }
    }

    /**
     * @param integer $playerId
     * @param integer $tournamentId
     * @param Phase   $originPhase
     *
     * @return Entrant|bool
     */
    protected function getEntrant($playerId, $tournamentId, Phase $originPhase)
    {
        if (isset($this->entrants[$tournamentId][$playerId])) {
            return $this->entrants[$tournamentId][$playerId];
        }

        $player = $this->getPlayerById($playerId);

        if ($player->getOriginTournament() === null) {
            $player->setOriginTournament($originPhase->getEvent()->getTournament());
        }

        $entrant = new Entrant();
        $entrant->setName($player->getGamerTag());
        $entrant->setIsNew(false);
        $entrant->addPlayer($player);
        $entrant->setOriginPhase($originPhase);
        $player->addEntrant($entrant);

        $this->entityManager->persist($entrant);

        $this->entrants[$tournamentId][$playerId] = $entrant;

        return $entrant;
    }

    /**
     * @return void
     */
    protected function countPlayers()
    {
        /** @var Tournament $tournament */
        foreach ($this->tournaments as $tournament) {
            $tournament->setPlayerCount();
        }
    }
}
