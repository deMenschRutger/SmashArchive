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
    public function postUpdate($entrant)
    {
        foreach ($entrant->getPlayers() as $player) {
            $this->cacheManager->onPlayerChange($player, true, true);
        }
    }

    /**
     * @param Entrant $entrant
     */
    public function preRemove($entrant)
    {
        foreach ($entrant->getPlayers() as $player) {
            $this->cacheManager->onPlayerChange($player, true, true);
        }
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
            ->select($rootAlias.', p, te, t')
            ->leftJoin($rootAlias.'.players', 'p')
            ->leftJoin($rootAlias.'.targetEntrant', 'te')
            ->leftJoin($rootAlias.'.originTournament', 't')
        ;

        return $query;
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
            ->add('targetEntrant', 'sonata_type_model_autocomplete', [
                'label' => 'Parent',
                'minimum_input_length' => 2,
                'property' => 'name',
                'required' => false,
                'callback' => function (AbstractAdmin $admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();

                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $datagrid->getQuery();
                    $rootAlias = $queryBuilder->getRootAlias();

                    $queryBuilder
                        ->select("{$rootAlias}, ot")
                        ->join("{$rootAlias}.originTournament", 'ot')
                        ->where("{$rootAlias}.{$property} LIKE :name")
                        ->setParameter('name', '%'.$value.'%')
                    ;
                },
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
            ->add('originTournament')
        ;
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('name')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('targetEntrant', null, [
                'label' => 'Parent',
            ])
            ->add('players')
            ->add('originTournament')
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
