<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\DataTransferObject\PlayerDTO;
use Domain\Command\Player\OverviewCommand;
use Domain\Handler\AbstractHandler;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class OverviewHandler extends AbstractHandler
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
     * @param OverviewCommand $command
     * @return array
     */
    public function handle(OverviewCommand $command)
    {
        $page = $command->getPage();
        $limit = $command->getLimit();
        $tag = $command->getTag();

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from('CoreBundle:Player', 'p')
            ->orderBy('p.gamerTag')
        ;

        if ($tag) {
            $queryBuilder->where('p.gamerTag LIKE :tag')->setParameter('tag', '%Ad%');
        }

        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate($queryBuilder->getQuery(), $page, $limit);
        $players = [];

        foreach ($pagination as $player) {
            $players[] = new PlayerDTO($player);
        }

        return [
            'pagination' => $pagination,
            'players' => $players,
        ];
    }
}
