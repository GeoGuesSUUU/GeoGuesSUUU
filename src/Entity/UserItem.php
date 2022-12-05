<?php

namespace App\Entity;

use App\Repository\UserItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserItemRepository::class)]
class UserItem
{

    #[ORM\Column]
    private int $quantity = 0;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'userItems')]
    private ItemType $itemType;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'userItems')]
    private User $user;

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
