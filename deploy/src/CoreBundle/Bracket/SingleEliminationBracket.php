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
                $name = 'Finals';
                break;
            case 1:
                $name = 'Semifinals';
                break;
            case 2:
                $name = 'Quarterfinals';
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

    /**
     * @return void
     */
    protected function init()
    {
        parent::init();

        // Make sure the rounds are in the right order.
        ksort($this->setsByRound);

        // Reset the indexes in case certain round numbers were skipped for some reason.
        $this->setsByRound = array_values($this->setsByRound);
    }
}