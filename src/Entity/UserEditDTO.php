<?php

namespace App\Entity;

class UserEditDTO extends UserSaveDTO
{
    private int $id;

    private ?int $coins;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getCoins(): ?int
    {
        return $this->coins;
    }

    /**
     * @param int|null $coins
     */
    public function setCoins(?int $coins): void
    {
        $this->coins = $coins;
    }

    public function edit(User $user): User
    {
        $user->setName($this->getName());
        $user->setEmail($this->getEmail());
        $user->setPassword($this->getPassword());
        $user->setCoins($this->coins);
        return $user;
    }
}