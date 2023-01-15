<?php

namespace App\Entity;

use Ratchet\ConnectionInterface;

class GameConnection
{
    private User $user;
    private ConnectionInterface $connection;

    /**
     * @param User $user
     * @param ConnectionInterface $connections
     */
    public function __construct(User $user, ConnectionInterface $connections)
    {
        $this->user = $user;
        $this->connection = $connections;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return GameConnection
     */
    public function setUser(User $user): GameConnection
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connections
     * @return GameConnection
     */
    public function setConnection(ConnectionInterface $connections): GameConnection
    {
        $this->connection = $connections;
        return $this;
    }

}
