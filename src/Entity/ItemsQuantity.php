<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class ItemsQuantity
{
    #[Groups(groups: ['response'])]
    private ItemType $item;

    #[Groups(groups: ['response'])]
    private int $quantity;

    /**
     * @return ItemType
     */
    public function getItem(): ItemType
    {
        return $this->item;
    }

    /**
     * @param ItemType $item
     * @return ItemsQuantity
     */
    public function setItem(ItemType $item): ItemsQuantity
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return ItemsQuantity
     */
    public function setQuantity(int $quantity): ItemsQuantity
    {
        $this->quantity = $quantity;
        return $this;
    }
}
