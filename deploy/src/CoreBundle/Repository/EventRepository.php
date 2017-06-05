<?php

declare(strict_types = 1);

namespace CoreBundle\Repository;

use CoreBundle\Entity\Event;
use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventRepository extends EntityRepository
{
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
            ->from('CoreBundle:Phase', 'p')
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
     * @param Event $event
     */
    public function deleteResults(Event $event)
    {
        $results = $this->_em->getRepository('CoreBundle:Result')->findBy([
            'event' => $event,
        ]);

        foreach ($results as $result) {
            $this->_em->remove($result);
        }

        $this->_em->flush();
    }
}
