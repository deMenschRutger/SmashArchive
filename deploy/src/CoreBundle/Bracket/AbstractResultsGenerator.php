<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractResultsGenerator
{
    /**
     * @return array
     */
    abstract public function getResults();
}
