<?php

namespace App\Form;

use App\Entity\ItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                    'skin' => 'TYPE_SKIN',
                    'attack' => 'TYPE_ATTACK',
                    'equipment' => 'TYPE_EQUIPMENT',
                    'support' => 'TYPE_SUPPORT',
                    'other' => 'TYPE_OTHER'
                ],
            ])
            ->add('rarity', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'common' => 'COMMON',
                    'uncommon' => 'UNCOMMON',
                    'rare' => 'RARE',
                    'epic' => 'EPIC',
                    'legendary' => 'LEGENDARY'
                ],
            ])
            ->add('fantastic')
            ->add('img')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemType::class,
        ]);
    }
}
