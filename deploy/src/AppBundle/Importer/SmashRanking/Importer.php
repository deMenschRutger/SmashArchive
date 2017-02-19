<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

use CoreBundle\Entity\Player;
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
    protected $players = [];

    /**
     * @var array
     */
    protected $tournaments = [];

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
            $entity = new Player();
            $entity->setGamerTag($player['tag']);

            $this->entityManager->persist($entity);
            $player = $entity;
        }

        return $players;
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
            $entity = new Tournament();
            $entity->setName($tournament['name']);
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
}