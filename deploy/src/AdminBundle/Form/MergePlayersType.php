<?php

declare(strict_types = 1);

namespace AdminBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class MergePlayersType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('targetPlayer', EntityType::class, [
                'class' => 'CoreBundle:Player',
                'label' => false,
                'multiple' => false,
                'placeholder' => 'Please select a player',
                'choice_label' => 'expandedGamerTag',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Merge players',
            ])
        ;
    }
}
