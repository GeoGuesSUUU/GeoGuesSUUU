<?php

namespace App\WebSocket;

use App\Repository\MessageRepository;
use App\Service\ChatService;
use App\Service\UserService;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class FindTheFlagGameHandler implements MessageComponentInterface
{
    protected SplObjectStorage $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        /** @var array $json */
        $json = json_decode($msg, true);
        switch ($json['event']) {
            case '@StartGame':
                break;
            case '@CreateOrJoinRoom':
                break;
            case '@GuessCountry':
                break;
            case '@FinishGame':
                break;
            default:
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->connections->detach($conn);
        $conn->close();
    }
}

