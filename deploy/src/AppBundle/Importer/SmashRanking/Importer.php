<?php

declare(strict_types = 1);

namespace AppBundle\Importer\SmashRanking;

use CoreBundle\Entity\Character;
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
        'NoPhasesMultipleEvents'       => true, // Cleared (273 tournaments)
        'NoPhasesSingleEventBracket'   => true, // Cleared (1057 tournaments)
        'NoPhasesSingleEventNoBracket' => true, // Cleared (11 tournaments)
        'PhasesMultipleEvents'         => true, // Cleared (114 tournaments)
        'PhasesSingleEventBracket'     => true, // Cleared (75 tournaments)
        'PhasesSingleEventNoBracket'   => true, // Cleared (1 tournament)
    ];

    /**
     * @param SymfonyStyle  $io
     * @param string        $contentDirPath
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

        foreach ($this->scenarios as $scenario => $active) {
            if (!$active) {
                continue;
            }

            $this->io->section("Importing tournaments for scenario '{$scenario}'...");
            $class = 'AppBundle\Importer\SmashRanking\\'.$scenario;

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

            // Some players had a country suffix in smashranking.eu to uniquely identify them (for example: Adam (NL)). This code removes
            // that suffix. Uniquely identifying a player is now based on slugs.
            $tag = $player['tag'];
            preg_match('~^(.*) \(([A-Z]{2})\)$~', $tag, $tagMatches);

            if (array_key_exists(1, $tagMatches)) {
                $tag = $tagMatches[1];
            }

            $entity = new Player();
            $entity->setOriginalId($playerId);
            $entity->setName($player['name'] ? $player['name'] : null);
            $entity->setGamerTag($tag);
            $entity->setNationality($nationality);
            $entity->setCountry($country);
            $entity->setRegion($player['region'] ? $player['region'] : null);
            $entity->setCity($player['city'] ?  $player['city'] : null);
            $entity->setIsActive(!$player['hide']);

            if ($player['main']) {
                $character = $this->getCharacterBySmashRankingId($player['main']);
                $entity->addMain($character);
            }

            if ($player['secondary']) {
                $character = $this->getCharacterBySmashRankingId($player['secondary']);
                $entity->addSecondary($character);
            }

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
            $entity->setDateStart(new \DateTime($tournament['date']));
            $entity->setCountry($country);
            $entity->setRegion($tournament['region']);
            $entity->setCity($tournament['city']);
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
        if (count($this->countries) === 0) {
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
     * @param int $id
     * @return Character
     */
    protected function getCharacterBySmashRankingId($id)
    {
        return $this->entityManager->find('CoreBundle:Character', $id);
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
