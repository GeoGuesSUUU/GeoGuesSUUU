<?php

namespace App\WebSocket;

use App\Repository\MessageRepository;
use App\Service\ChatService;
use App\Service\UserService;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class ChatHandler implements MessageComponentInterface
{
    protected ChatService $chatService;
    protected SplObjectStorage $connections;

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly UserService       $userService,
    )
    {
        $this->connections = new SplObjectStorage;
        $this->chatService = new ChatService(
            $this->messageRepository,
            $this->userService,
            $this->connections
        );
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
            case '@SendMessage':
            {
                $res = $this->chatService->addMessageToDB($json);
                $this->chatService->sendToEveryoneExceptSrc($from, json_encode($res));
                break;
            }
            case '@GetMessages':
            {
                $res = $this->chatService->getMessages();
                $from->send(json_encode($res));
                break;
            }
            case '@GetCountConnection':
                $res = $this->chatService->getCountConnection();
                $from->send(json_encode($res));
                break;
            default:
                $this->chatService->sendToEveryone($json);
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

