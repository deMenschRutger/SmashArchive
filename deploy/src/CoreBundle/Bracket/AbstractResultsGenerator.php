<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractResultsGenerator
{
    /**
     * @var AbstractBracket
     */
    protected $bracket;

    /**
     * @param AbstractBracket $bracket
     */
    public function __construct(AbstractBracket $bracket)
    {
        $this->bracket = $bracket;
    }

    /**
     * @return array
     */
    abstract public function getResults();
}
