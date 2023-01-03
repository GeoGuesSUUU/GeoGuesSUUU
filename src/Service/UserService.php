<?php

namespace App\Service;

use App\Entity\ItemType;
use App\Entity\User;
use App\Entity\UserItem;
use App\Repository\UserItemRepository;
use App\Repository\UserRepository;

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
            $link->setQuantity($link->getQuantity() + $quantity);
        }

        $this->userItemRepository->save($link, $flush);

        return $user;
    }

    /**
     * @param User $user
     * @param int $itemId
     * @param bool $flush
     * @return void
     */
    public function removeItemById(User $user, int $itemId, bool $flush = false): void
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
                $link->setQuantity($link->getQuantity() - 1);
                $this->userItemRepository->save($link, $flush);
            }

        }
    }
}
