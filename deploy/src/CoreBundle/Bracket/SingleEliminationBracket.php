<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SingleEliminationBracket extends AbstractBracket
{
    /**
     * @var array
     */
    protected $setsById = [];

    /**
     * @var array
     */
    protected $setsByRound = [];

    /**
     * @var array
     */
    protected $entrants = [];

    /**
     * @param Set $set
     */
    public function determineRoundName(Set $set)
    {
        $entrantCount = count($this->entrants);
        $totalRounds = ceil(log($entrantCount, 2));
        $reverseIndex = intval($totalRounds) - $set->getRound();

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
        $entrantCount = count($this->entrants);
        $totalRounds = ceil(log($entrantCount, 2));
        $reverseIndex = intval($totalRounds) - $set->getRound();

        if ($reverseIndex === 0) {
            $set->setIsGrandFinals(true);
        }
    }

    /**
     * @return void
     */
    protected function init()
    {
        /** @var Set $set */
        foreach ($this->phaseGroup->getSets() as $set) {
            $setId = $set->getId();
            $round = $set->getRound();

            $this->addEntrant($set->getEntrantOne());
            $this->addEntrant($set->getEntrantTwo());

            $this->setsById[$setId] = $set;
            $this->setsByRound[$round][] = $set;
        }

        // Make sure the rounds are in the right order.
        ksort($this->setsByRound);

        // Reset the indexes in case certain round numbers were skipped for some reason.
        $this->setsByRound = array_values($this->setsByRound);
    }

    /**
     * @param Entrant $entrant
     */
    protected function addEntrant($entrant)
    {
        if (!$entrant instanceof Entrant) {
            return;
        }

        if (!in_array($entrant, $this->entrants, true)) {
            $this->entrants[] = $entrant;
        }
    }
}