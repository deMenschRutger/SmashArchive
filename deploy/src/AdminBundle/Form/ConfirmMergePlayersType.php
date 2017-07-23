<?php

declare(strict_types = 1);

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ConfirmMergePlayersType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('confirm', CheckboxType::class, [
                'label' => "I confirm that I'm aware of the consequences of merging these two players.",
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Merge players',
            ])
        ;
    }
}
