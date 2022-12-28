<?php

namespace App\Entity;

class ClaimRewards
{
    private int $coins = 0;

    /**
     * @var ItemsQuantity[] $items
     */
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
