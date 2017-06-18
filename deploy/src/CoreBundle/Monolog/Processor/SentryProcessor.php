<?php

declare(strict_types = 1);

namespace CoreBundle\Monolog\Processor;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SentryProcessor
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @param string $environment
     */
    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param array $record
     * @return array
     */
    public function processRecord(array $record)
    {
        // Add the environment tag.
        if (!array_key_exists('tags', $record['extra'])) {
            $record['extra']['tags'] = [];
        }

        $record['extra']['tags']['environment'] = $this->environment;

        // Check if we need to process a Guzzle Exception.
        if (array_key_exists('exception', $record['context']) &&
            $record['context']['exception'] instanceof RequestException
        ) {
            /** @var RequestException $exception */
            $exception = $record['context']['exception'];
            $response  = $exception->getResponse();

            if ($response instanceof ResponseInterface) {
                $record['extra']['response'] = [
                    'body' => (string) $response->getBody(),
                    'code' => $response->getStatusCode(),
                    'headers' => json_encode($response->getHeaders(), JSON_PRETTY_PRINT),
                    'reasonPhrase' => $response->getReasonPhrase(),
                ];
            }
        }

        return $record;
    }
}
