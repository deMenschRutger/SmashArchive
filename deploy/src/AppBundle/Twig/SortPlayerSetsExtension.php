<?php

declare(strict_types = 1);

namespace AppBundle\Twig;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Set;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SortPlayerSetsExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('sort_player_sets', [$this, 'sortPlayerSets']),
        ];
    }

    /**
     * @param array $sets
     * @return array
     */
    public function sortPlayerSets($sets)
    {
        $winnersBracketSets = [];
        $losersBracketSets = [];
        $grandFinals = [];

        /** @var Set $set */
        foreach ($sets as $set) {
            // This happens if at least one of the entrants is a bye, or if the set hasn't finished yet.
            if (!$set->getEntrantOne() instanceof Entrant ||
                !$set->getEntrantTwo() instanceof Entrant
            ) {
                continue;
            }

            if ($set->getIsGrandFinals()) {
                $grandFinals[] = $set;
            } elseif ($set->getRound() > 0) {
                $winnersBracketSets[] = $set;
            } elseif ($set->getRound() < 0) {
                $losersBracketSets[] = $set;
            }
        }

        usort($losersBracketSets, function (Set $setA, Set $setB) {
            if ($setA->getRound() === $setB->getRound()) {
                return 0;
            }

            return $setA->getRound() < $setB->getRound();
        });

        return array_merge($winnersBracketSets, $losersBracketSets, $grandFinals);
    }
}