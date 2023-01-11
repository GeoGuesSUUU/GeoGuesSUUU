<?php

namespace App\Service;

use App\Entity\FindTheFlagRoom;
use App\Entity\GameConnection;
use App\Entity\GameRoom;
use App\Entity\GameRoomMember;
use App\Entity\User;
use App\Utils\GameRoomVisibility;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class FindTheFlagGameService extends WebSocketService
{
    private GameRoomService $gameRoomService;

    public function __construct(
        private readonly UserService       $userService,
        SplObjectStorage                   $connections
    )
    {
        parent::__construct($connections);
        $this->gameRoomService = new GameRoomService();
    }

    /**
     * @param GameRoom $room
     * @param User $user
     * @return void
     */
    public function connectRoomEmit(GameRoom $room, User $user): void
    {
        $res = [
            'event' => '@NewRoomConnection',
            'user' => GameRoomMember::convertUser($user)
        ];

        foreach ($room->getConnections() as $connection) {
            if ($connection->getUser()->getId() === $user->getId()) continue;
            $connection->getConnection()->send(json_encode($res));
        }
    }

    /**
     * @param GameRoom $room
     * @param User $user
     * @return void
     */
    public function leaveRoomEmit(GameRoom $room, User $user): void
    {
        $res = [
            'event' => '@RemoveRoomConnection',
            'user' => GameRoomMember::convertUser($user)
        ];

        foreach ($room->getConnections() as $connection) {
            if ($connection->getUser()->getId() === $user->getId()) continue;
            $connection->getConnection()->send(json_encode($res));
        }
    }

    public function createOrJoinRoom(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['roomName'];

        try {
            $user = $this->userService->getById($data['user']['id']);

            $room = $this->gameRoomService->getRoomByName($roomName);
            if (is_null($room)) {
                $room = $this->gameRoomService->createFindTheFlagRoom($roomName, GameRoomVisibility::PUBLIC);
            }

            $newConnection = new GameConnection($user, $from);
            $room->addConnection($newConnection);

            $this->connectRoomEmit($room, $user);

            return [
                'event' => '@RoomCreatedOrJoined',
                'name' => $room->getName(),
                'members' => $this->gameRoomService->getMembers()
            ];
        } catch (Exception $ex) {
            //
            return [
                'exception' => [
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage()
                ]
            ];
        }
    }

    public function LeaveRoom(ConnectionInterface $from, array $data): array | null
    {
        $roomName = $data['roomName'];
        $room = $this->gameRoomService
            ->getRoomByName($roomName)?->removeConnectionByConnectionInterface($from);

        if (is_null($room)) return null;

        try {
            $user = $this->userService->getById($data['user']['id']);

            if (count($room->getConnections()) <= 0) {
                $this->gameRoomService->removeRoomByName($roomName);
            } else {
                $this->leaveRoomEmit($room, $user);
            }
            return [
                'event' => '@RoomLeaved',
                'name' => $roomName
            ];
        } catch (Exception $ex) {
            //
            return [
                'exception' => [
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage()
                ]
            ];
        }
    }

    public function createPrivateRoomAndStartGame(ConnectionInterface $from, array $data): array
    {
        try {
            $user = $this->userService->getById($data['user']['id']);
            $room = $this->gameRoomService->createFindTheFlagRoom(null, GameRoomVisibility::PRIVATE);

            $newConnection = new GameConnection($user, $from);
            $room->addConnection($newConnection);

            $room->initGame($data['difficulty']);

            return [
                'event' => '@GameStart',
                'room' => $room->getName(),
                'guess' => $room->getGuess()
            ];
        } catch (Exception $ex) {
            //
            return [
                'exception' => [
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage()
                ]
            ];
        }
    }
}
