<?php

declare(strict_types = 1);

namespace AdminBundle\Form;

use CoreBundle\Service\Smashgg\Smashgg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ImportSmashggType extends AbstractType
{
    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @param Smashgg $smashgg
     */
    public function __construct(Smashgg $smashgg)
    {
        $this->smashgg = $smashgg;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('smashggId');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $events = $this->smashgg->getTournamentEvents($options['smashggId'], true);
        $choices = [];

        foreach ($events as $event) {
            $name = $event['name'];
            $choices[$name] = $event['id'];
        }

        $builder
            ->add('events', ChoiceType::class, [
                'choices' => $choices,
                'constraints' => [
                    new Count([
                        'min' => 1,
                    ]),
                ],
                'expanded' => true,
                'label' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add to queue',
            ])
        ;
    }
}
