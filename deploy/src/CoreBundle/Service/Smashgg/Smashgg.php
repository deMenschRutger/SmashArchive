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
        $response = $client->get($slug, [
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
     * @return array
     */
    public function getTournamentVideogames($slug)
    {
        return $this->getTournamentEntities($slug, ['event'])['videogame'];
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
        return $this->getTournamentEntities($slug, ['group'])['group'];
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => 'https://api.smash.gg/tournament/',
            ]);
        }

        return $this->client;
    }
}
