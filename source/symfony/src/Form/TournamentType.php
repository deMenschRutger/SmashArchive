<?php

declare(strict_types = 1);

namespace App\Form;

use App\Entity\Country;
use App\Entity\Tournament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => Tournament::class,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('country', EntityType::class, [
                'class' => Country::class,
            ])
            ->add('region', TextType::class)
            ->add('city', TextType::class)
            ->add('dateStart', DateTimeType::class, [
                'widget' => 'single_text',
                'input'  => 'string',
            ])
            ->add('dateEnd', DateTimeType::class, [
                'widget' => 'single_text',
                'input'  => 'string',
            ])
        ;

        $dateTimeTransformer = new CallbackTransformer(
            function ($asDateTime) {
                return $asDateTime instanceof \DateTime ? $asDateTime->format('Y-m-d h:i:s') : $asDateTime;
            },
            function ($asString) {
                return $asString instanceof \DateTime ? $asString : new \DateTime($asString);
            }
        );

        $builder->get('dateStart')->addModelTransformer($dateTimeTransformer);
        $builder->get('dateEnd')->addModelTransformer($dateTimeTransformer);
    }
}
