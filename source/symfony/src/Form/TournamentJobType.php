<?php

declare(strict_types = 1);

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentJobType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('provider', ChoiceType::class, [
                'choices'  => [Tournament::SOURCE_CHALLONGE, Tournament::SOURCE_SMASHGG],
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
            ])
            ->add('events', CollectionType::class, [
                'allow_add'  => true,
                'entry_type' => IntegerType::class,
            ])
        ;
    }
}
