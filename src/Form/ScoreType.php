<?php

namespace App\Form;

use App\Entity\Level;
use App\Entity\Score;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score')
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'input'=> 'datetime_immutable'
            ])
            ->add('time')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' =>  function (User $user) {
                    return $user->getName() . ' ' . $user->getEmail();
                },
                'required' => true
            ])
            ->add('level', EntityType::class, [
                'class' => Level::class,
                'choice_label' =>  function (Level $level) {
                    return $level->getLabel() . ' ' . $level->getDifficulty();
                },
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);
    }
}
