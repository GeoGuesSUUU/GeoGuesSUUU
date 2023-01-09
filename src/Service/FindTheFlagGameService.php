<?php

namespace App\Service;

use App\Entity\User;
use App\Utils\CountriesISO;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindTheFlagGameService extends WebSocketService
{
    private array $rooms;

    public function __construct(
        private readonly UserService       $userService,
        SplObjectStorage                   $connections
    )
    {
        parent::__construct($connections);
    }

    /**
     * @throws Exception
     */
    public function startGame(array $data): array
    {
        $guessCount = (int) ($data['difficulty'] * 3);
        $isoArray = [];
        $isoAvailable = CountriesISO::countriesCases();
        for ($i = 0; $i < $guessCount; $i++) {
            $rand = random_int(0, count($isoAvailable) - 1);
            $iso = $isoAvailable[$rand];

            if (in_array(strtolower($iso->name), $isoArray)) continue;
            $isoArray[] = strtolower($iso->name);
        }

        return [
            'event' => '@GameStart',
            'guess' => $isoArray
        ];
    }

    public function connectRoomEmit(array $room, User $user) {
        $connections = array_map(fn($conn) => $conn['connection'], $room);
        $res = [
            'event' => '@NewRoomConnection',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'isVerified' => $user->isVerified()
            ]
        ];

        /** @var ConnectionInterface $connection */
        foreach ($connections as $connection) {
            $connection->send(json_encode($res));
        }
    }

    public function leaveRoomEmit(array $room, User $user) {
        $connections = array_map(fn($conn) => $conn['connection'], $room);
        $res = [
            'event' => '@RemoveRoomConnection',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'isVerified' => $user->isVerified()
            ]
        ];

        /** @var ConnectionInterface $connection */
        foreach ($connections as $connection) {
            $connection->send(json_encode($res));
        }
    }

    public function createOrJoinRoom(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['roomName'];
        if (is_null($roomName) || strlen($roomName) < 1) {
            return [
                'exception' => [
                    'code' => 401,
                    'message' => 'Invalid Room Name'
                ]
            ];
        }

        try {
            $user = $this->userService->getById($data['user']['id']);

            $room = $this->rooms[$roomName];
            $this->connectRoomEmit($room, $user);
            $connection = [
                'user' => $user,
                'connection' => $from
            ];
            if (is_null($room)) {
                $this->rooms[$roomName] = [
                    'name' => $roomName,
                    'isPrivate' => false,
                    'connections' => $connection
                ];
            } else {
                $room['connections'][] = $connection;
            }

            return [
                'event' => '@RoomCreatedOrJoined',
                'name' => $roomName,
                'members' => array_map(fn($conn) => [
                    'id' => $conn['user']->getId(),
                    'name' => $conn['user']->getName(),
                    'isAdmin' => in_array('ROLE_ADMIN', $conn['user']->getRoles()),
                    'isVerified' => $conn['user']->isVerified()
                ],$room)
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

    public function LeaveRoom(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['roomName'];
        if (is_null($roomName) || strlen($roomName) < 1) {
            return [
                'exception' => [
                    'code' => 401,
                    'message' => 'Invalid Room Name'
                ]
            ];
        }

        try {
            $user = $this->userService->getById($data['user']['id']);

            $room = $this->rooms[$roomName];
            $this->leaveRoomEmit($room, $user);
            if (is_null($room)) {
                throw new NotFoundHttpException();
            } else {
                $key = array_search(
                    $user->getId(),
                    array_map(fn($conn) => $conn['user']->getId(), $room['connections'])
                );
                unset($this->rooms[$roomName]['connections'][$key]);
                return [
                    'event' => '@RoomLeaved',
                    'name' => $roomName
                ];
            }
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
