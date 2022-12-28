<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class ClaimRewards
{
    #[Groups(groups: ['response'])]
    private int $coins = 0;

    /**
     * @var ItemsQuantity[] $items
     */
    #[Groups(groups: ['response'])]
    private array $items = [];

    /**
     * @return int
     */
    public function getCoins(): int
    {
        return $this->coins;
    }

    /**
     * @param int $coins
     * @return ClaimRewards
     */
    public function setCoins(int $coins): ClaimRewards
    {
        $this->coins = $coins;
        return $this;
    }

    /**
     * @return ItemsQuantity[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ItemsQuantity[] $items
     * @return ClaimRewards
     */
    public function setItems(array $items): ClaimRewards
    {
        $this->items = $items;
        return $this;
    }
}
