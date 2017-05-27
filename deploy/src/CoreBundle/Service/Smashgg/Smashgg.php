<?php

declare(strict_types = 1);

namespace CoreBundle\Service\Smashgg;

use GuzzleHttp\Client;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Smashgg
{
    const VALID_GAME_IDS = [
        1, // Super Smash Bros. Melee,
        3, // Super Smash Bros. for Wii U,
    ];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param string $slug
     * @param array  $expand
     * @return array
     */
    public function getTournamentEntities($slug, array $expand = [])
    {
        $client = $this->getClient();
        $response = $client->get('tournament/'.$slug, [
            'query' => [
                'expand' => $expand,
            ],
        ]);

        $raw = (string) $response->getBody();
        $body = \GuzzleHttp\json_decode($raw, true);

        return $body['entities'];
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getTournament($slug)
    {
        return $this->getTournamentEntities($slug)['tournament'];
    }

    /**
     * @param string $slug
     * @param bool   $filterInvalidGames
     * @return array
     */
    public function getTournamentEvents($slug, $filterInvalidGames = false)
    {
        $events = $this->getTournamentEntities($slug, ['event'])['event'];

        if ($filterInvalidGames) {
            $events = array_filter($events, function ($event) {
                return in_array($event['videogameId'], self::VALID_GAME_IDS);
            });
        }

        return $events;
    }

    /**
     * @param string $slug
     * @param bool   $filterInvalidGames
     * @return array
     */
    public function getTournamentVideogames($slug, $filterInvalidGames = false)
    {
        $games =  $this->getTournamentEntities($slug, ['event'])['videogame'];

        if ($filterInvalidGames) {
            $games = array_filter($games, function ($game) {
                return in_array($game['id'], self::VALID_GAME_IDS);
            });
        }

        return $games;
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getTournamentPhases($slug)
    {
        return $this->getTournamentEntities($slug, ['phase'])['phase'];
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getTournamentGroups($slug)
    {
        return $this->getTournamentEntities($slug, ['groups'])['groups'];
    }

    /**
     * @param int   $id
     * @param array $expand
     * @return array
     */
    public function getPhaseGroup($id, array $expand = [])
    {
        $client = $this->getClient();
        $response = $client->get('phase_group/'.$id, [
            'query' => [
                'expand' => $expand,
            ],
        ]);

        $raw = (string) $response->getBody();
        $body = \GuzzleHttp\json_decode($raw, true);

        return $body['entities'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getPhaseGroupSets($id)
    {
        return $this->getPhaseGroup($id, ['sets'])['sets'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getPhaseGroupEntrants($id)
    {
        return $this->getPhaseGroup($id, ['entrants'])['entrants'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getPhaseGroupPlayers($id)
    {
        return $this->getPhaseGroup($id, ['entrants'])['player'];
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => 'https://api.smash.gg',
            ]);
        }

        return $this->client;
    }
}
