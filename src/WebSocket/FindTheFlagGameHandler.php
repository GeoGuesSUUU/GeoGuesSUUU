<?php

namespace App\WebSocket;

use App\Repository\LevelRepository;
use App\Repository\ScoreRepository;
use App\Service\FindTheFlagGameService;
use App\Service\UserService;
use Exception;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class FindTheFlagGameHandler implements MessageComponentInterface
{
    protected SplObjectStorage $connections;
    private FindTheFlagGameService $findTheFlagGameService;

    public function __construct(
        private readonly LoggerInterface $logger,
        UserService $userService,
        LevelRepository $levelRepository,
        ScoreRepository   $scoreRepository,
    )
    {
        $this->connections = new SplObjectStorage;
        $this->findTheFlagGameService = new FindTheFlagGameService(
            $userService,
            $levelRepository,
            $scoreRepository,
            $this->connections
        );
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);
        $this->logger->info('Find The Flag - Connection Opened');
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
                $res = $this->findTheFlagGameService->leaveRoom($from, $json);
                $from->send(json_encode($res));
                break;
            case '@GuessCountry':
                $res = $this->findTheFlagGameService->guessCountry($from, $json);
                $from->send(json_encode($res));
                break;
            case '@FinishGame':
                $res = $this->findTheFlagGameService->finishGame($from, $json);
                $from->send(json_encode($res));
                break;
            default:
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
        $this->findTheFlagGameService->extractConnection($conn);
        $this->logger->info('Find The Flag - Connection Error');
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->connections->detach($conn);
        $this->findTheFlagGameService->extractConnection($conn);
        $conn->close();
        $this->logger->error('Find The Flag - Connection Closed');
    }
}

