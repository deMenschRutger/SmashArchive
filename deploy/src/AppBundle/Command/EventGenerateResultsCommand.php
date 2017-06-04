<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Entity\Phase;
use Doctrine\ORM\EntityManager;
use Domain\Command\Event\GenerateResultsCommand;
use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventGenerateResultsCommand extends ContainerAwareCommand
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:event:generate:results')
            ->setDescription('Generate the complete results for an entire event.')
            ->addArgument(
                'event-id',
                InputArgument::REQUIRED,
                'The ID of the event you wish to generate results for.'
            );
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     *
     * @TODO Add option to generate results for all events at once.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = intval($input->getArgument('event-id'));
        $command = new GenerateResultsCommand($eventId);
        $this->commandBus->handle($command);
    }

    /**
     * @return void
     */
    protected function processAllEvent()
    {
        /** @var Phase[] $phases */
        $phases = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p, pg, s')
            ->from('CoreBundle:Phase', 'p')
            ->join('p.phaseGroups', 'pg')
            ->join('pg.sets', 's')
            ->join('p.event', 'e')
            ->addOrderBy('s.round')
            ->getQuery()
            ->getResult()
        ;
    }
}
