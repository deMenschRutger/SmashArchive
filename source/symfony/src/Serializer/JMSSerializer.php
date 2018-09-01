<?php

declare(strict_types = 1);

namespace App\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use MediaMonks\RestApi\Serializer\JMSSerializer as MediaMonksJMSSerializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class JMSSerializer extends MediaMonksJMSSerializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array|null
     */
    private $groups = null;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array|string|null $groups
     */
    public function setGroups($groups = null)
    {
        if (is_string($groups)) {
            $groups = [$groups];
        }

        $this->groups = $groups;
    }

    /**
     * @param mixed  $data
     * @param string $format
     *
     * @return string
     */
    public function serialize($data, $format = 'json')
    {
        $context = SerializationContext::create();
        $context->setSerializeNull(true);

        if (is_array($this->groups)) {
            $context->setGroups($this->groups);
        }

        return $this->serializer->serialize($data, $format, $context);
    }
}
