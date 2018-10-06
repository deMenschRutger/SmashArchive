<?php

declare(strict_types = 1);

namespace App\Tests\Serializer;

use App\Service\Smashgg\Smashgg;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashggTest extends TestCase
{
    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @return void
     */
    public function setUp()
    {
        // TODO Replace this with the actual response from the smash.gg API.
        $body = \GuzzleHttp\json_encode([
            'entities' => [
                'foo' => 'bar',
            ],
        ]);

        $mock = new MockHandler([
            new Response(202, [], $body),
        ]);

        $this->smashgg = new Smashgg($mock);
    }

    /**
     * @return void
     */
    public function testGetTournamentEntities404(): void
    {
        $entities = $this->smashgg->getTournamentEntities('non-existing-tournament');

        self::assertEquals(['foo' => 'bar'], $entities);
    }
}
