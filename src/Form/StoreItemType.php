<?php

namespace App\Form;

use App\Entity\ItemType;
use App\Entity\StoreItem;
use App\Repository\ItemTypeRepository;
use App\Repository\StoreItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class StoreItemType extends AbstractType
{
    private StoreItemRepository $storeItemRepository;
    private ItemTypeRepository $itemTypeRepository;

    public function __construct(ItemTypeRepository $itemTypeRepository, StoreItemRepository $storeItemRepository)
    {
        $this->storeItemRepository = $storeItemRepository;
        $this->itemTypeRepository = $itemTypeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'manual' => 'manual',
                    'auto' => 'auto',
                ]
            ])
            ->add('trending', CheckboxType::class, [
                'label' => 'On trend',
                'required' => false
            ])
            ->add('promotion', RangeType::class, [
                'constraints' => [
                    new Range(['min' => 0, 'max' => 100]),
                ],
                'attr' => [
                    'oninput' => 'document.querySelector("#range-value").innerHTML = this.value;'
                ],
                'data' => 0
            ])
            ->add('itemType', EntityType::class, [
                'class' => ItemType::class,
                'query_builder' => function () {
                    $unAvailableItems = $this->storeItemRepository->findAll();

                    if (sizeof($unAvailableItems) === 0) {
                        return $this->itemTypeRepository->createQueryBuilder('i');
                    }

                    $unAvailableItemsId = [];

                    foreach ($unAvailableItems as $item) {
                        $unAvailableItemsId[] = $item->getItemType()->getId();
                    }

                    $stringIds = implode(', ', $unAvailableItemsId);

                    return $this->itemTypeRepository
                        ->createQueryBuilder('i')
                        ->where(sprintf('i.id not in (%s)', $stringIds));
                },
                'choice_label' => function (ItemType $itemType) {
                    return $itemType->getName();
                },
                'required' => false,
                'placeholder' => '',
                'empty_data' => null,
                'label' => 'Item'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StoreItem::class,
        ]);
    }
}
