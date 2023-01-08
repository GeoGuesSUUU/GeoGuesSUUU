<?php

namespace App\Form;

use App\Entity\Country;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeImmutableToDateTimeTransformer;
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
            ->add('continent')
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
        ]);
    }
}
