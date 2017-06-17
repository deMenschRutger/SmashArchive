<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class CharacterAdmin extends AbstractAdmin
{
    /**
     * @var int
     */
    protected $maxPerPage = 100;

    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_by' => 'name',
    ];

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Basics')
            ->add('name')
            ->add('game')
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
            ->add('game')
        ;
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('id')
            ->add('name')
            ->add('game')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('game')
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
