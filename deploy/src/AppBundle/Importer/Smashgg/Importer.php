<?php

declare(strict_types = 1);

namespace AppBundle\Importer\Smashgg;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Client;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $slug
     */
    public function import(string $slug)
    {
        $client = new Client();
        $response = $client->get('https://api.smash.gg/'.$slug, [
            'query' => [
                'expand' => ['event', 'phase', 'groups'],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $tournamentData = $apiData['entities']['tournament'];
    }

    /**
     * @param int $smashGgId
     * @return Player
     */
    protected function findPlayer(int $smashGgId): Player
    {
        $player = $this->getRepository('CoreBundle:Player')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$player instanceof Player) {
            $player = new Player();
            $player->setSmashggId($smashGgId);

            $this->entityManager->persist($player);
        }

        return $player;
    }

    /**
     * @param int $smashGgId
     * @return Set
     */
    protected function findSet(int $smashGgId): Set
    {
        $set = $this->getRepository('CoreBundle:Set')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$set instanceof Set) {
            $set = new Set();
            $set->setSmashggId($smashGgId);

            $this->entityManager->persist($set);
        }

        return $set;
    }

    /**
     * @param int $smashGgId
     * @return Entrant
     */
    protected function findEntrant(int $smashGgId): Entrant
    {
        $entrant = $this->getRepository('CoreBundle:Entrant')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setSmashggId($smashGgId);

            $this->entityManager->persist($entrant);
        }

        return $entrant;
    }
    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository(string $entityName): EntityRepository
    {
        return $this->entityManager->getRepository($entityName);
    }
}
