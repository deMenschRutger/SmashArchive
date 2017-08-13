<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use CoreBundle\Entity\Player;
use Psr\Cache\CacheItemPoolInterface as Cache;
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
     * @var Cache|TaggableCacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_by' => 'gamerTag',
    ];

    /**
     * @param Cache|TaggableCacheItemPoolInterface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Player $object
     */
    public function postUpdate($object)
    {
        $this->cache->invalidateTag($object->getCacheTag());
    }

    /**
     * @param Player $object
     */
    public function postRemove($object)
    {
        $this->cache->invalidateTag($object->getCacheTag());
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
            ->add('isNew')
            ->end()
            ->with('Merge with player')
            ->add('targetPlayer', 'sonata_type_model_autocomplete', [
                'minimum_input_length' => 2,
                'property' => 'gamerTag',
                'required' => false,
                'to_string_callback' => function (Player $entity) {
                    return $entity->getExpandedGamerTag();
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
            ->add('gamerTag')
            ->add('name')
            ->add('region')
            ->add('city')
            ->add('country')
            ->add('nationality')
            ->add('isActive')
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
            ->add('smashggId')
            ->add('isCompeting')
            ->add('isActive')
            ->add('isNew')
            ->add('originTournament')
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
            ->add('isNew')
        ;

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                    'show' => [],
                    'merge' => [
                        'template' => 'AdminBundle:Player:list__action_merge.html.twig',
                    ],
                    'delete' => [],
                ],
                'label' => 'Actions',
            ]
        );
    }
}
