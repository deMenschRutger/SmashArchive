<?php

declare(strict_types = 1);

namespace App\Form\Player;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ProfileType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => Profile::class,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gamerTag', TextType::class, [
                'required' => true,
            ])
            ->add('name', TextType::class)
            ->add('region', TextType::class)
            ->add('city', TextType::class)
            ->add('isCompeting', CheckboxType::class, [
                'required' => true,
            ])
            ->getForm()
        ;
    }
}
