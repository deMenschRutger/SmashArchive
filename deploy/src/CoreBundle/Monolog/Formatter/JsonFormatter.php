<?php

declare(strict_types = 1);

namespace CoreBundle\Monolog\Formatter;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class JsonFormatter extends \Monolog\Formatter\JsonFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        $json = parent::toJson($data, $ignoreErrors);
        $data = json_decode($json);

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
