<?php

namespace App\Entity;

use App\Utils\GameRoomVisibility;
use Ratchet\ConnectionInterface;

class GameRoom
{
    private string $name;

    private GameRoomVisibility $visibility;

    /** @var GameConnection[] array  */
    private array $connections;

    /**
     * @param string $name
     * @param GameRoomVisibility $visibility
     */
    public function __construct(string $name, GameRoomVisibility $visibility)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->connections = [];
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
     * @return GameRoom
     */
    public function setName(string $name): GameRoom
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return GameRoomVisibility
     */
    public function getVisibility(): GameRoomVisibility
    {
        return $this->visibility;
    }

    /**
     * @param GameRoomVisibility $visibility
     * @return GameRoom
     */
    public function setVisibility(GameRoomVisibility $visibility): GameRoom
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param array $connections
     * @return GameRoom
     */
    public function setConnections(array $connections): GameRoom
    {
        $this->connections = $connections;
        return $this;
    }

    public function addConnection(GameConnection $connection): GameRoom
    {
        $this->connections[] = $connection;
        return $this;
    }

    public function addConnections(GameConnection ...$connections): GameRoom
    {
        foreach ($connections as $conn) {
            $this->connections[] = $conn;
        }
        return $this;
    }

    public function removeConnectionByConnectionInterface(ConnectionInterface $connection): GameRoom
    {
        $this->connections = array_filter($this->connections, fn($conn) => $conn->getConnection() !== $connection);
        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_map(fn($conn) => $conn->getUser(), $this->connections);
    }
}
