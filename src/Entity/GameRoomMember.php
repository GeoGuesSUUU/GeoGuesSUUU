<?php

namespace App\Entity;

class GameRoomMember
{
    private int $id;
    private string $name;
    private bool $admin;
    private bool $verified;

    /**
     * @param int $id
     * @param string $name
     * @param bool $admin
     * @param bool $verified
     */
    public function __construct(int $id, string $name, bool $admin = false, bool $verified = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->admin = $admin;
        $this->verified = $verified;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GameRoomMember
     */
    public function setId(int $id): GameRoomMember
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GameRoomMember
     */
    public function setName(string $name): GameRoomMember
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     * @return GameRoomMember
     */
    public function setAdmin(bool $admin): GameRoomMember
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * @param bool $verified
     * @return GameRoomMember
     */
    public function setVerified(bool $verified): GameRoomMember
    {
        $this->verified = $verified;
        return $this;
    }

    public static function convertUser(User $user): GameRoomMember
    {
        return new GameRoomMember(
            $user->getId(),
            $user->getName(),
            $user->isAdmin(),
            $user->isVerified()
        );
    }

    public static function convertUserToArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'isAdmin' => $user->isAdmin(),
            'isVerified' => $user->isVerified(),
            'img' => $user->getImg()
        ];
    }
}
