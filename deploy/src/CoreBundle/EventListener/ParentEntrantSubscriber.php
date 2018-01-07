<?php

declare(strict_types = 1);

namespace CoreBundle\EventListener;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Phase;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * @author Rutger Mensch <rutger.mensch@mediamonks.com>
 */
class ParentEntrantSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Entrant) {
                continue;
            }

            $changeSet = $unitOfWork->getEntityChangeSet($entity);

            if (!array_key_exists('parentEntrant', $changeSet)) {
                continue;
            }

            /** @var Entrant $oldParentEntrant */
            $oldParentEntrant = $changeSet['parentEntrant'][0];

            if ($oldParentEntrant === null) {
                $oldParentEntrant = $entity;
            }

            /** @var Entrant $newParentEntrant */
            $newParentEntrant = $changeSet['parentEntrant'][1];

            if ($newParentEntrant === null) {
                $newParentEntrant = $entity;
            }

            $this->updateParentEntrantSets($oldParentEntrant, $newParentEntrant, $entity->getOriginPhase(), $entityManager);
        }

        $unitOfWork->computeChangeSets();
    }

    /**
     * @param Entrant                $oldParentEntrant
     * @param Entrant                $newParentEntrant
     * @param Phase                  $originPhase
     * @param EntityManagerInterface $entityManager
     */
    protected function updateParentEntrantSets($oldParentEntrant, $newParentEntrant, $originPhase, $entityManager)
    {
        $sets = $entityManager
            ->getRepository('CoreBundle:Set')
            ->findByEntrantIdAndPhaseId($oldParentEntrant->getId(), $originPhase->getId())
        ;

        foreach ($sets as $set) {
            if ($set->getEntrantOne() === $oldParentEntrant) {
                $set->setEntrantOne($newParentEntrant);
            } elseif ($set->getEntrantTwo() === $oldParentEntrant) {
                $set->setEntrantTwo($newParentEntrant);
            }

            if ($set->getWinner() === $oldParentEntrant) {
                $set->setWinner($newParentEntrant);
            } elseif ($set->getLoser() === $oldParentEntrant) {
                $set->setLoser($newParentEntrant);
            }
        }
    }
}
