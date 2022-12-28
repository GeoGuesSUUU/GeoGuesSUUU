<?php

namespace App\Service;

use App\Entity\ItemType;
use App\Entity\User;
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
