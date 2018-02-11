<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\SetRepository;
use Doctrine\ORM\Query;
use Domain\Command\Player\SetsCommand;
use Domain\Handler\AbstractHandler;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsHandler extends AbstractHandler
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param SetsCommand $command
     * @return PaginationInterface|array
     */
    public function handle(SetsCommand $command)
    {
        $slug = $command->getSlug();
        $eventId = $command->getEventId();
        $page = $command->getPage();
        $limit = $command->getLimit();

        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');

        if ($eventId) {
            $query = $repository->findByProfileSlugAndEventId($slug, $eventId);
        } else {
            // TODO Add option to filter by even type.
            $query = $repository->findByProfileSlug($slug, 'all');
        }

        if ($command->getSortByPhase()) {
            return $this->getSetsByPhaseId($query);
        }

        return $this->paginator->paginate($query, $page, $limit);
    }

    /**
     * @param Query $query
     * @return array
     */
    protected function getSetsByPhaseId(Query $query)
    {
        $sets = $query->getResult();
        $setsByPhaseId = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phaseGroup = $set->getPhaseGroup();
            $phase = $phaseGroup->getPhase();
            $phaseId = $phase->getId();

            if (!array_key_exists($phaseId, $setsByPhaseId)) {
                $setsByPhaseId[$phaseId] = [
                    'name' => $phase->getName(),
                    'sets'  => [],
                ];
            }

            $setsByPhaseId[$phaseId]['sets'][] = $set;
        }

        return $setsByPhaseId;
    }
}
