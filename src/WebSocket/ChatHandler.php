<?php

namespace App\WebSocket;

use App\Service\ChatService;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class ChatHandler implements MessageComponentInterface
{
    protected ChatService $chatService;

    public function __construct(
        protected SplObjectStorage $connections
    )
    {
        $this->chatService = new ChatService($this->connections);
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
            case '@Message':
                $this->chatService->sendToEveryoneExceptSrc($from, $msg);
                break;
            case '@Connection':
                $this->chatService->sendToEveryone($this->connections->count());
                break;
            default:
                $this->chatService->sendToEveryone($msg);
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

