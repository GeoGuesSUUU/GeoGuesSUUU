<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Level;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('difficulty')
            ->add('label')
            ->add('description')
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' =>  'title',
                'required' => false,
                'placeholder' => '',
                'empty_data' => null
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
