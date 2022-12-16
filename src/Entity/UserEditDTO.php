<?php

namespace App\Entity;

class UserEditDTO extends UserSaveDTO
{
    private int $id;

    private ?int $coins;

    private array $roles;

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
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function edit(User $user): User
    {
        $user->setName($this->getName());
        $user->setEmail($this->getEmail());
        $user->setPassword($this->getPassword());
        $user->setCoins($this->coins);
        $user->setRoles($this->roles);
        return $user;
    }
}