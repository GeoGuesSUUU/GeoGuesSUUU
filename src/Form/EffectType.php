<?php

namespace App\Form;

use App\Entity\Effect;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EffectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value')
            ->add('type', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'life-' => 'life-',
                    'shield-' => 'shield-',
                    'price-' => 'price-',
                    'claim-' => 'claim-',
                    'life+' => 'life+',
                    'life_max+' => 'life_max+',
                    'shield+' => 'shield+',
                    'shield_max+' => 'shield_max+',
                    'price+' => 'price+',
                    'claim+' => 'claim+',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Effect::class,
        ]);
    }
}
