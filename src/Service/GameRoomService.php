<?php

namespace App\Service;

use App\Entity\FindTheFlagRoom;
use App\Entity\GameConnection;
use App\Entity\GameRoom;
use App\Entity\GameRoomMember;
use App\Entity\Level;
use App\Entity\User;
use App\Utils\GameRoomVisibility;
use Ratchet\ConnectionInterface;

class GameRoomService
{
    /** @var (GameRoom | FindTheFlagRoom)[] array  */
    public array $rooms;

    public function __construct()
    {
        $this->rooms = [];
    }

    /**
     * @param string|null $name
     * @param Level $level
     * @param GameRoomVisibility $visibility
     * @param GameConnection ...$connection
     * @return GameRoom
     */
    public function createRoom(
        ?string $name,
        Level $level,
        GameRoomVisibility $visibility = GameRoomVisibility::PUBLIC,
        GameConnection ...$connection
    ): GameRoom
    {
        if (is_null($name) || strlen($name) < 1) {
            $name = 'Room' . uniqid();
        }
        $newRoom = new GameRoom($name, $level, $visibility);
        $newRoom->addConnections(...$connection);
        $this->rooms[] = $newRoom;
        return $newRoom;
    }

    /**
     * @param string|null $name
     * @param Level $level
     * @param GameRoomVisibility $visibility
     * @param GameConnection ...$connection
     * @return FindTheFlagRoom
     */
    public function createFindTheFlagRoom(
        ?string $name,
        Level $level,
        GameRoomVisibility $visibility = GameRoomVisibility::PUBLIC,
        GameConnection ...$connection
    ): FindTheFlagRoom
    {
        if (is_null($name) || strlen($name) < 1) {
            $name = 'Room' . uniqid();
        }
        $newRoom = new FindTheFlagRoom($name, $level, $visibility);
        $newRoom->addConnections(...$connection);
        $this->rooms[$newRoom->getName()] = $newRoom;
        return $newRoom;
    }

    /**
     * @return array
     */
    public function getRooms(): array
    {
        return $this->rooms;
    }

    /**
     * @param string $name
     * @return GameRoom | FindTheFlagRoom | null
     */
    public function getRoomByName(string $name): GameRoom | FindTheFlagRoom | null
    {
        return $this->rooms[$name] ?? null;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_map(fn($conn) => $conn->getUsers(), $this->rooms);
    }

    /**
     * @return GameRoomMember[]
     */
    public function getMembers(): array
    {
        return array_map(fn($conn) => GameRoomMember::convertUser($conn->getUsers()) , $this->rooms);
    }

    public function removeRoomByName(string $name): GameRoomService
    {
        $this->rooms = array_filter($this->rooms, fn($room) => $room->getName() !== $name);
        return $this;
    }

    public function extract(ConnectionInterface $from): void
    {
        foreach ($this->rooms as $room) {
            $room->removeConnectionByConnectionInterface($from);
        }
    }

}
