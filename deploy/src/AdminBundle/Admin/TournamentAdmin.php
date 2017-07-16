<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use CoreBundle\Entity\Tournament;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateStart',
    ];

    /**
     * @param Tournament $tournament
     */
    public function prePersist($tournament)
    {
        $tournament->setEvents($tournament->getEvents());
    }

    /**
     * @param Tournament $tournament
     */
    public function preUpdate($tournament)
    {
        $tournament->setEvents($tournament->getEvents());
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Basics')
            ->add('name')
            ->add('country')
            ->add('region')
            ->add('city')
            ->add('dateStart')
            ->add('organizers')
            ->add('series')
            ->add('isActive')
            ->end()
            ->end()
            ->with('Additional information')
            ->add('smashggUrl')
            ->add('facebookEventUrl')
            ->add('resultsPage')
            ->end()
            ->with('Events')
            ->add(
                'events',
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
            ->add('region')
            ->add('country')
            ->add('city')
            ->add('dateStart')
            ->add('isActive')
        ;
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Basics')
            ->add('name')
            ->add('slug')
            ->add('location')
            ->add('country')
            ->add('region')
            ->add('city')
            ->add('dateStart')
            ->add('organizers')
            ->add('isActive')
            ->end()
            ->with('Additional information')
            ->add('smashggUrl')
            ->add('facebookEventUrl')
            ->add('resultsPage')
            ->end()
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('country')
            ->add('dateStart')
            ->add('isActive')
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
