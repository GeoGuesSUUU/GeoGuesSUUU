<?php

namespace App\WebSocket;

use App\Repository\MessageRepository;
use App\Service\ChatService;
use App\Service\FindTheFlagGameService;
use App\Service\UserService;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class FindTheFlagGameHandler implements MessageComponentInterface
{
    protected SplObjectStorage $connections;
    private FindTheFlagGameService $findTheFlagGameService;

    public function __construct(
        UserService $userService,
    )
    {
        $this->connections = new SplObjectStorage;
        $this->findTheFlagGameService = new FindTheFlagGameService($userService, $this->connections);
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
            case '@StartGameSingle':
                $res = $this->findTheFlagGameService->createPrivateRoomAndStartGame($from, $json);
                $from->send(json_encode($res));
                break;
            case '@CreateOrJoinRoom':
                $res = $this->findTheFlagGameService->createOrJoinRoom($from, $json);
                $from->send(json_encode($res));
                break;
            case '@LeaveRoom':
                $res = $this->findTheFlagGameService->LeaveRoom($from, $json);
                $from->send(json_encode($res));
                break;
//            case '@GuessCountry':
//                break;
//            case '@FinishGame':
//                break;
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

