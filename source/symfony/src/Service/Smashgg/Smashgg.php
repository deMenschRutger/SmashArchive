<?php

declare(strict_types = 1);

namespace App\Service\Smashgg;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Smashgg
{
    const VALID_GAME_IDS = [
        1, // Super Smash Bros. Melee,
        2, // Project M
        3, // Super Smash Bros. for Wii U,
        4, // Super Smash Bros.
        5, // Super Smash Bros. Brawl
    ];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param string $slug
     * @param array  $expand
     *
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
     *
     * @return array
     */
    public function getTournament($slug)
    {
        return $this->getTournamentEntities($slug)['tournament'];
    }

    /**
     * @param string $slug
     * @param bool   $filterInvalidGames
     *
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
     *
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
     *
     * @return array
     */
    public function getTournamentPhases($slug)
    {
        return $this->getTournamentEntities($slug, ['phase'])['phase'];
    }

    /**
     * @param string $slug
     * @param array  $phaseIds
     *
     * @return array
     */
    public function getTournamentGroups($slug, $phaseIds = null)
    {
        $groups = $this->getTournamentEntities($slug, ['groups'])['groups'];

        return array_filter($groups, function ($group) use ($phaseIds) {
            if (!is_array($phaseIds)) {
                return true;
            }

            return in_array($group['phaseId'], $phaseIds);
        });
    }

    /**
     * @param int   $id
     * @param array $expand
     *
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
     *
     * @return array
     */
    public function getPhaseGroupSets($id)
    {
        return $this->getPhaseGroup($id, ['sets'])['sets'];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getPhaseGroupEntrants($id)
    {
        return $this->getPhaseGroup($id, ['entrants'])['entrants'];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getPhaseGroupPlayers($id)
    {
        return $this->getPhaseGroup($id, ['entrants'])['player'];
    }

    /**
     * @param int              $retries
     * @param Request          $request
     * @param Response         $response
     * @param RequestException $exception
     *
     * @return bool
     */
    public function retryDecider($retries, $request, $response = null, $exception = null)
    {
        if ($retries >= 5) {
            return false;
        }

        if ($exception instanceof ConnectException) {
            return true;
        }

        if ($response && $response->getStatusCode() >= 500) {
            return true;
        }

        return false;
    }

    /**
     * @param int $numberOfRetries
     *
     * @return int
     */
    public function retryDelay($numberOfRetries)
    {
        return 500 * $numberOfRetries;
    }

    /**
     * @return Client
     *
     * @TODO Perhaps we can use the APCu cache for the production environment? We would need to clean it regularly though.
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->cache = new ArrayCache();
            $cache = new GreedyCacheStrategy(new DoctrineCacheStorage($this->cache), 3600);

            $handlerStack = HandlerStack::create(new CurlHandler());
            $handlerStack->push(Middleware::retry([$this, 'retryDecider'], [$this, 'retryDelay']), 'retry');
            $handlerStack->push(new CacheMiddleware($cache), 'cache');

            $this->client = new Client([
                'base_uri' => 'https://api.smash.gg',
                'handler' => $handlerStack,
            ]);
        }

        return $this->client;
    }
}
