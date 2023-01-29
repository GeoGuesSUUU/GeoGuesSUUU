<?php

namespace App\Form;

use App\Entity\ItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add('price', IntegerType::class, [
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                    new Assert\LessThanOrEqual(['value' => 4000000000]),
                ],
                'attr' => [
                    'min' => 0,
                    'max' => 4000000000,
                ]
            ])
            ->add('effects', CollectionType::class, [
                'entry_type' => EffectType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemType::class,
            'cascade_validation' => true
        ]);
    }
}
