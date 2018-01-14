<?php

declare(strict_types = 1);

namespace AdminBundle\Admin;

use CoreBundle\Entity\Player;
use CoreBundle\Utility\CacheManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerAdmin extends AbstractAdmin
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_by' => 'name',
    ];

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Player $player
     */
    public function postUpdate($player)
    {
        $this->cacheManager->onPlayerChange($player);
    }

    /**
     * @param Player $player
     */
    public function preRemove($player)
    {
        $this->cacheManager->onPlayerChange($player);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('type', 'choice', [
                'choices' => [
                    'custom'          => Player::SOURCE_CUSTOM,
                    'smash.gg'        => Player::SOURCE_SMASHGG,
                    'Challonge'       => Player::SOURCE_CHALLONGE,
                    'TIO'             => Player::SOURCE_TIO,
                    'smashranking.eu' => Player::SOURCE_SMASHRANKING,
                ],
            ])
            ->add('externalId', null, [
                'disabled' => true,
            ])
            ->add('originTournament', 'sonata_type_model_autocomplete', [
                'property' => 'name',
                'required' => false,
            ])
            ->add('playerProfile', 'sonata_type_model_autocomplete', [
                'minimum_input_length' => 2,
                'property'             => 'gamerTag',
                'required'             => false,
            ])
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('originTournament.name')
            ->add('playerProfile.gamerTag')
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
            ->add('type')
            ->add('externalId')
            ->add('originTournament')
            ->add('playerProfile')
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
            ->addIdentifier('name')
            ->add('type')
            ->add('originTournament')
            ->add('playerProfile')
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
