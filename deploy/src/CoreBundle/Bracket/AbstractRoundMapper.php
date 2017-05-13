<?php

declare(strict_types = 1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Set;

/**
 * The purpose of a round mapper is to determine details about individual bracket rounds, such as the round name and whether or not it is
 * a grand finals set.
 *
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractRoundMapper
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
     * @param Set $set
     */
    public function determineRoundName(Set $set)
    {
        $mappedRound = $this->getMappedRound($set);

        if (!$mappedRound) {
            return;
        }

        $set->setRoundName($mappedRound['name']);
    }

    /**
     * @param Set $set
     */
    public function determineIsGrandFinals(Set $set)
    {
        $mappedRound = $this->getMappedRound($set);

        if (!$mappedRound) {
            return;
        }

        $set->setIsGrandFinals($mappedRound['isGrandFinals']);
    }

    /**
     * @return void
     */
    abstract protected function load();

    /**
     * @param Set $set
     * @return int
     */
    abstract protected function getMappedRound(Set $set);
}
