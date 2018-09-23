<?php

declare(strict_types = 1);

namespace App\Tests\Serializer;

use App\Serializer\JMSSerializer;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class JMSSerializerTest extends TestCase
{
    /**
     * @var JMSSerializer
     */
    protected $serializer;

    /**
     * @return void
     */
    public function setUp()
    {
        $JMSSerializer = SerializerBuilder::create()->build();

        $this->serializer = new JMSSerializer($JMSSerializer);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $actual = $this->serializer->serialize([
            'gamerTag' => 'Armada',
            'country'  => null,
        ]);

        self::assertEquals('{"gamerTag":"Armada","country":null}', $actual);
    }
}
