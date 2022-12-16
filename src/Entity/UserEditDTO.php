<?php

namespace App\Entity;

class UserEditDTO extends UserSaveDTO
{
    private int $id;

    private ?int $coins;

    private ?bool $admin;

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

    /**
     * @return bool|null
     */
    public function getAdmin(): ?bool
    {
        return $this->admin;
    }

    /**
     * @param bool|null $admin
     */
    public function setAdmin(?bool $admin): void
    {
        $this->admin = $admin;
    }

    public function edit(User $user): User
    {
        $user->setName($this->getName());
        $user->setEmail($this->getEmail());
        $user->setPassword($this->getPassword());
        $user->setCoins($this->coins);
        $user->setAdmin($this->admin);
        return $user;
    }
}