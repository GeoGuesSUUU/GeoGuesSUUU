<?php

namespace App\Entity;

use App\Repository\CountryItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryItemRepository::class)]
class CountryItem
{

    #[ORM\Column]
    private int $quantity = 0;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'countryItems')]
    private Country $country;

    #[ORM\Id]
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
