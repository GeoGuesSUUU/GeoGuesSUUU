<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\User;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeImmutableToDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('code')
            ->add('flag')
            ->add('continent', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'Asia' => 'asia',
                    'Africa' => 'africa',
                    'North America' => 'north-america',
                    'South America' => 'south-america',
                    'Antarctica' => 'antarctica',
                    'Oceania' => 'oceania',
                    'Europe' => 'europe'
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' =>  function (User $user) {
                    return $user->getName() . ' ' . $user->getEmail();
                },
                'required' => false,
                'placeholder' => '',
                'empty_data' => null,
            ])
            ->add('initLife')
            ->add('life')
            ->add('lifeMax')
            ->add('shield')
            ->add('shieldMax')
            ->add('initPrice')
            ->add('claimDate', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable'
            ])
            ->add('ownedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable'
            ])
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
            'data_class' => Country::class,
        ]);
    }
}
