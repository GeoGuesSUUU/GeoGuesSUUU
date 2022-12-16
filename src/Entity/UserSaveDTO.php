<?php

namespace App\Entity;

class UserSaveDTO
{
    private ?string $name;

    private ?string $email;

    private ?string $password;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function verify(): bool
    {

        return !isset($body->name) ||
            !isset($body->email) ||
            !isset($body->password);
    }

    public function toUser(): User
    {
        $user = new User();
        $user->setName($this->name);
        $user->setEmail($this->email);
        $user->setPassword($this->password);
        return $user;
    }
}