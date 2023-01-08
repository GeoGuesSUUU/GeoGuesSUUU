<?php

namespace App\Service;

use Ratchet\ConnectionInterface;
use SplObjectStorage;

class ChatService
{
    private SplObjectStorage $connections;

    public function __construct(
        SplObjectStorage $connections
    )
    {
        $this->connections = $connections;
    }

    public function sendToEveryone(mixed $data): void
    {
        foreach($this->connections as $connection)
        {
            $connection->send($data);
        }
    }

    public function sendToEveryoneExceptSrc(ConnectionInterface $from, mixed $data): void
    {
        foreach($this->connections as $connection)
        {
            if($connection === $from)
            {
                continue;
            }
            $connection->send($data);
        }
    }
}
