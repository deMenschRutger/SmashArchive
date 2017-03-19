<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SingleEliminationBracket extends AbstractBracket
{
    /**
     * @param Set $set
     */
    public function determineRoundName(Set $set)
    {
        $reverseIndex = $this->getReverseIndex($set);

        switch ($reverseIndex) {
            case 0:
                $name = 'Grand finals';
                break;
            case 1:
                $name = 'Winners semifinals';
                break;
            case 2:
                $name = 'Winners quarterfinals';
                break;
            default:
                $name = 'Round '.$set->getRound();
                break;
        }

        $set->setRoundName($name);
    }

    /**
     * @param Set $set
     */
    public function determineIsGrandFinals(Set $set)
    {
        if ($this->getReverseIndex($set) === 0) {
            $set->setIsGrandFinals(true);
        }
    }
}