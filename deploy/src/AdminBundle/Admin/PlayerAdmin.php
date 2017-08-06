<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_by' => 'gamerTag',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('merge', $this->getRouterIdParameter().'/merge');
        $collection->add('confirm_merge', $this->getRouterIdParameter().'/confirm-merge/{targetId}');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Basics')
            ->add('gamerTag')
            ->add('name')
            ->add('slug')
            ->add('region')
            ->add('city')
            ->add('country')
            ->add('nationality')
            ->end()
            ->with('Characters')
            ->add('mains')
            ->add('secondaries')
            ->end()
            ->with('Status')
            ->add('isActive')
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('gamerTag')
            ->add('name')
            ->add('region')
            ->add('city')
            ->add('country')
            ->add('nationality')
            ->add('isActive')
            ->add('isNew')
        ;
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('id')
            ->add('slug')
            ->add('gamerTag')
            ->add('name')
            ->add('region')
            ->add('city')
            ->add('country')
            ->add('nationality')
            ->add('mains')
            ->add('secondaries')
            ->add('isCompeting')
            ->add('isActive')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('gamerTag')
            ->add('name')
            ->add('country')
        ;

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                    'merge' => [
                        'template' => 'AdminBundle:Player:list__action_merge.html.twig',
                    ],
                    'show' => [],
                    'delete' => [],
                ],
                'label' => 'Actions',
            ]
        );
    }
}
