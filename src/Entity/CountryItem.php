<?php

namespace App\Entity;

use App\Repository\CountryItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CountryItemRepository::class)]
class CountryItem
{

    #[ORM\Column]
    #[Groups(groups: ['country_item_api_response', 'country_item_anti_cr'])]
    private int $quantity = 0;

    #[ORM\Id]
    #[Groups(groups: ['country_item_api_response'])]
    #[ORM\ManyToOne(inversedBy: 'countryItems')]
    private Country $country;

    #[ORM\Id]
    #[Groups(groups: ['country_item_api_response', 'country_item_anti_cr'])]
    #[ORM\ManyToOne(inversedBy: 'countryItems')]
    private ItemType $itemType;


    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getItemType(): ItemType
    {
        return $this->itemType;
    }

    public function setItemType(ItemType $itemType): self
    {
        $this->itemType = $itemType;

        return $this;
    }
}
