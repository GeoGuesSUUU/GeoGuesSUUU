<?php

namespace App\Service;

use App\Entity\StoreItem;
use App\Entity\User;
use App\Exception\StoreItemNotFoundApiException;
use App\Exception\UserNotValidApiException;
use App\Repository\StoreItemRepository;

class StoreService
{
    public function __construct(
        private readonly StoreItemRepository $storeItemRepository,
        private readonly UserService $userService
    )
    {
    }

    /**
     * @param StoreItem $storeItem
     * @return StoreItem
     */
    public function calculateItemPrice(StoreItem $storeItem): StoreItem
    {
        return $storeItem->setPromoPrice(
            $storeItem->getItemType()->getPrice() * (1 - $storeItem->getPromotion() / 100)
        );
    }

    /**
     * @param StoreItem ...$storeItems
     * @return StoreItem[]
     */
    public function calculateItemsPrice(StoreItem ...$storeItems): array
    {
        foreach ($storeItems as $storeItem) {
            $storeItem->setPromoPrice(
                $storeItem->getItemType()->getPrice() * (1 - $storeItem->getPromotion() / 100)
            );
        }
        return $storeItems;
    }

    /**
     * @return StoreItem[]
     */
    public function getAll(): array
    {
        return $this->calculateItemsPrice(
            ...$this->storeItemRepository->findAll()
        );
    }

    /**
     * @param int $id
     * @return StoreItem
     * @throws StoreItemNotFoundApiException
     */
    public function getById(int $id): StoreItem
    {
        /** @var StoreItem $store */
        $store = $this->storeItemRepository->findOneBy(['id' => $id]);
        if (is_null($store)) throw new StoreItemNotFoundApiException();
        return $this->calculateItemPrice($store);
    }

    /**
     * @param StoreItem $storeItem
     * @param bool $flush
     * @return StoreItem
     */
    public function save(StoreItem $storeItem, bool $flush = false): StoreItem
    {
        $this->storeItemRepository->save($storeItem, $flush);
        return $storeItem;
    }

    public function buy(User $user, int $id, int $quantity = 1): User
    {

        $item = $this->getById($id);
        if ($user->getCoins() < $item->getPromoPrice()) {
            throw new UserNotValidApiException("Invalid User coins");
        }
        $user->setCoins($user->getCoins() - $item->getPromoPrice());
        $user = $this->userService->addItemInInventory($user, $item->getItemType(), $quantity);
        $this->userService->save($user, true);
        return $user;
    }

}
