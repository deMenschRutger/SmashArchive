<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventRepository extends EntityRepository
{
    /**
     * @return integer[]
     */
    public function getAllEventIds()
    {
        $events = $this
            ->createQueryBuilder('e')
            ->select('e.id')
            ->join('e.tournament', 't')
            ->where('t.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult()
        ;

        return array_map(function (array $event) {
            return $event['id'];
        }, $events);
    }

    /**
     * @param int $eventId
     * @return array
     */
    public function getOrderedPhases($eventId)
    {
        return $this
            ->_em
            ->createQueryBuilder()
            ->select('p, pg, s, en1, en2, w, l')
            ->from('App:Phase', 'p')
            ->join('p.phaseGroups', 'pg')
            ->join('pg.sets', 's')
            ->leftJoin('s.entrantOne', 'en1')
            ->leftJoin('s.entrantTwo', 'en2')
            ->leftJoin('s.winner', 'w')
            ->leftJoin('s.loser', 'l')
            ->join('p.event', 'e')
            ->where('e.id = ?1')
            ->setParameter(1, $eventId)
            ->addOrderBy('p.phaseOrder, s.round')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $eventId
     * @return int
     */
    public function countUniqueEntrants($eventId)
    {
        return (int) $this
            ->_em
            ->createQueryBuilder()
            ->select('COUNT(en.id)')
            ->from('App:Entrant', 'en')
            ->join('en.originPhase', 'op')
            ->join('op.event', 'ev')
            ->where('ev.id = :id')
            ->andWhere('en.parentEntrant IS NULL')
            ->setParameter('id', $eventId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Event $event
     */
    public function deleteResults(Event $event)
    {
        $results = $this->_em->getRepository('App:Result')->findBy([
            'event' => $event,
        ]);

        foreach ($results as $result) {
            $this->_em->remove($result);
        }

        $this->_em->flush();
    }
}
