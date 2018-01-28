<?php

declare(strict_types = 1);

namespace CoreBundle\Repository;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerRepository extends EntityRepository
{
    /**
     * @param string $slug
     * @return Player[]
     */
    public function findOpponents(string $slug)
    {
        /** @var Set[] $sets */
        $sets = $this
            ->_em
            ->getRepository('CoreBundle:Set')
            ->findByPlayerSlug($slug)
            ->getResult()
        ;

        $opponents = [];
        $iterator = function ($entrant) use (&$opponents) {
            if (!$entrant instanceof Entrant) {
                return;
            }

            foreach ($entrant->getPlayers() as $player) {
                if (!in_array($player, $opponents)) {
                    $opponents[] = $player;
                }
            }
        };

        foreach ($sets as $set) {
            $iterator($set->getEntrantOne());
            $iterator($set->getEntrantTwo());
        }

        return $opponents;
    }
}
