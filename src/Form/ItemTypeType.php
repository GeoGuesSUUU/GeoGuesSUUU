<?php

namespace App\Form;

use App\Entity\ItemType;
use App\Form\EffectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('type', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'skin' => 'skin',
                    'attack' => 'attack',
                    'equipment' => 'equipment',
                    'support' => 'support',
                    'other' => 'other'
                ],
            ])
            ->add('rarity', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'common' => 'common',
                    'uncommon' => 'uncommon',
                    'rare' => 'rare',
                    'epic' => 'epic',
                    'legendary' => 'legendary'
                ],
            ])
            ->add('fantastic')
            ->add('img')
            ->add('effects', CollectionType::class, [
                'entry_type' => EffectType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemType::class,
        ]);
    }
}
