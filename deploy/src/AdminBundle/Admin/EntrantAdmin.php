<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Player;
use CoreBundle\Utility\CacheManager;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EntrantAdmin extends AbstractAdmin
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Entrant $entrant
     */
    public function preUpdate($entrant)
    {
        $this->cacheManager->onEntrantChange($entrant);
    }

    /**
     * @param Entrant $entrant
     */
    public function preRemove($entrant)
    {
        $this->cacheManager->onEntrantChange($entrant);
    }

    /**
     * @param string $context
     * @return QueryBuilder
     */
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        $rootAlias = $query->getRootAliases()[0];

        $query
            ->select($rootAlias.', p, pe, op, oe, t')
            ->leftJoin($rootAlias.'.players', 'p')
            ->leftJoin($rootAlias.'.parentEntrant', 'pe')
            ->leftJoin($rootAlias.'.originPhase', 'op')
            ->leftJoin('op.event', 'oe')
            ->leftJoin('oe.tournament', 't');
        ;

        return $query;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $alias
     * @param string       $field
     * @param mixed        $value
     * @return bool
     */
    public function filterTournamentName($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $queryBuilder->andWhere('t.name LIKE :name');
        $queryBuilder->setParameter('name', '%'.$value['value'].'%');

        return true;
    }

    /**
     * @param AbstractAdmin $admin
     * @param string        $property
     * @param mixed         $value
     */
    public function completeParentEntrant(AbstractAdmin $admin, $property, $value)
    {
        $datagrid = $admin->getDatagrid();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $datagrid->getQuery();
        $rootAlias = $queryBuilder->getRootAlias();

        $queryBuilder
            ->where("{$rootAlias}.{$property} LIKE :name")
            ->andWhere("{$rootAlias}.originPhase IS NOT NULL")
            ->setParameter('name', '%'.$value.'%')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('merge', $this->getRouterIdParameter().'/merge');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Entrant $entrant */
        $entrant = $this->getSubject();
        $event = $entrant->getOriginPhase()->getEvent();

        $formMapper
            ->with('Basics')
            ->add('id', null, [
                'disabled' => true,
            ])
            ->add('name')
            ->add('isNew')
            ->add('players', 'sonata_type_model_autocomplete', [
                'minimum_input_length' => 2,
                'multiple' => true,
                'property' => 'gamerTag',
                'required' => false,
                'to_string_callback' => function (Player $entity) {
                    return $entity->getExpandedGamerTag();
                },
            ])
            ->end()
            ->with('Parent Entrant')
            ->add('parentEntrant', 'sonata_type_model_autocomplete', [
                'callback' => [$this, 'completeParentEntrant'],
                'label' => 'Parent',
                'help' => join([
                    'Please note: configuring a parent entrant and saving this form will assign all matches played by this entrant to the',
                    ' parent entrant. This action can not be undone.',
                ]),
                'minimum_input_length' => 2,
                'property' => 'name',
                'required' => false,
                'route' => [
                    'name' => 'admin_core_event_entrants',
                    'parameters' => [
                        'id' => $event->getId(),
                        'id' => $event->getId(),
                    ],
                ],
                'to_string_callback' => function (Entrant $entity) {
                    return $entity->getExpandedName();
                },
            ])
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('isNew')
            ->add('originPhase.name', null, [
                'label' => 'Origin phase',
            ])
            ->add('originTournament', 'doctrine_orm_callback', [
                'callback'   => [$this, 'filterTournamentName'],
                'field_type' => 'text',
                'label'      => 'Origin tournament',
            ])
        ;
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('name')
            ->add('tournament')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('name')
            ->add('parentEntrant', null, [
                'label' => 'Parent',
            ])
            ->add('players')
            ->add('originPhase.name', null, [
                'label' => 'Phase',
            ])
            ->add('originPhase.event.name', null, [
                'label' => 'Event',
            ])
            ->add('originPhase.event.tournament.name', null, [
                'label' => 'Tournament',
            ])
            ->add('isNew', null, [
                'editable' => true,
            ])
        ;

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                    'show' => [],
                ],
                'label' => 'Actions',
            ]
        );
    }
}
