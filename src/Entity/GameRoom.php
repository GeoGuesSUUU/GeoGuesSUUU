<?php

namespace App\Entity;

use App\Utils\GameRoomVisibility;
use Ratchet\ConnectionInterface;

class GameRoom
{
    private string $name;

    private Level $level;

    private GameRoomVisibility $visibility;

    /** @var GameConnection[] $connections  */
    private array $connections;

    /**
     * @param string $name
     * @param Level $level
     * @param GameRoomVisibility $visibility
     */
    public function __construct(string $name, Level $level, GameRoomVisibility $visibility)
    {
        $this->name = $name;
        $this->level = $level;
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
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * @param Level $level
     * @return GameRoom
     */
    public function setLevel(Level $level): GameRoom
    {
        $this->level = $level;
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
