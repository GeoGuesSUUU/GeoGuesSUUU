<?php

namespace App\Service;

use App\Entity\ItemsQuantity;
use App\Entity\ItemType;
use App\Entity\User;
use App\Entity\UserItem;
use App\Exception\ItemFantasticAlreadyExistApiException;
use App\Repository\UserItemRepository;
use App\Repository\UserRepository;
use Exception;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserItemRepository $userItemRepository,
    )
    {
    }

    public function flush(): void
    {
        $this->userRepository->flush();
    }

    /**
     * @param User $user
     * @param int $itemId
     * @return ItemType|null
     */
    public function findItemById(User $user, int $itemId): ItemType | null
    {
        return $this->userItemRepository->findOneBy([
            'user' => $user->getId(),
            'itemType' => $itemId
        ])?->getItemType();
    }

    public function addItemInInventory(User $user, ItemType $item, int $quantity = 1, bool $flush = false): User
    {
        $link = $this->userItemRepository->findOneBy([
            'user' => $user->getId(),
            'itemType' => $item->getId()
        ]);

        if (is_null($link)) {
            $link = new UserItem();
            $link->setItemType($item);
            $link->setQuantity($quantity);
            $link->setUser($user);
        } else {
            if ($link->getItemType()->isFantastic()) {
                throw new ItemFantasticAlreadyExistApiException();
            }
            $link->setQuantity($link->getQuantity() + $quantity);
        }

        $this->userItemRepository->save($link, $flush);

        return $user;
    }

    /**
     * @param User $user
     * @param ItemsQuantity[] $items
     * @param bool $flush
     * @return void
     */
    public function addItemsInInventory(User $user, array $items, bool $flush = false): void
    {
        foreach ($items as $item) {
            try {
                $this->addItemInInventory($user, $item->getItem(), $item->getQuantity());
            } catch (Exception $ex) {
                continue;
            }
        }
        $this->userRepository->save($user, $flush);
    }

    /**
     * @param User $user
     * @param int $itemId
     * @param int $quantity
     * @param bool $flush
     * @return void
     */
    public function removeItemById(User $user, int $itemId, int $quantity = 1, bool $flush = false): void
    {
        $link = $this->userItemRepository->findOneBy([
            'user' => $user->getId(),
            'itemType' => $itemId
        ]);

        if (!is_null($link)) {

            if ($link->getQuantity() === 1) {
                $this->userItemRepository->remove($link, $flush);
            }
            else {
                $link->setQuantity($link->getQuantity() - $quantity);
                $this->userItemRepository->save($link, $flush);
            }

        }
    }
}
