<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseAdmin extends AbstractAdmin
{
    /**
     * @param Phase $phase
     */
    public function prePersist($phase)
    {
        $phase->setPhaseGroups($phase->getPhaseGroups());
    }

    /**
     * @param Phase $phase
     */
    public function preUpdate($phase)
    {
        $phase->setPhaseGroups($phase->getPhaseGroups());
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Basics')
            ->add('name')
            ->add('phaseOrder')
            ->add('event', 'sonata_type_model_autocomplete', [
                'minimum_input_length' => 2,
                'property' => 'name',
                'required' => false,
                'callback' => function (AbstractAdmin $admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();

                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $datagrid->getQuery();
                    $rootAlias = $queryBuilder->getRootAlias();

                    $queryBuilder
                        ->select("{$rootAlias}, t")
                        ->join("{$rootAlias}.tournament", 't')
                        ->orWhere('t.name LIKE :tournament')
                        ->setParameter('tournament', '%'.$value.'%')
                    ;
                },
                'to_string_callback' => function (Event $entity) {
                    return $entity->getExpandedName();
                },
            ])
            ->end()
            ->with('Phase Groups')
            ->add(
                'phaseGroups',
                'sonata_type_collection',
                [
                    'label' => false,
                ],
                [
                    'edit' => 'inline',
                    'inline' => 'table',
                ]
            )
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
            ->add('event')
            ->add('event.tournament', null, [
                'label' => 'Tournament',
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
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('phaseOrder')
            ->add('event')
            ->add('event.tournament', null, [
                'label' => 'Tournament',
            ])
        ;

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                    'show' => [],
                    'delete' => [],
                ],
                'label' => 'Actions',
            ]
        );
    }
}
