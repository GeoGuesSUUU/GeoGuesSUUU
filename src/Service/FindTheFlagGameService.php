<?php

namespace App\Service;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class FindTheFlagGameService
{
    private SplObjectStorage $connections;

    private array $rooms;

    public function __construct(
        private readonly UserService       $userService,
        SplObjectStorage                   $connections
    )
    {
        $this->connections = $connections;
    }

    function createOrJoinRoom(ConnectionInterface $from, ?string $roomName): array
    {
        if (is_null($roomName) || strlen($roomName) < 1) {

        }
        $room = $this->rooms[$roomName];
        if (is_null($room)) {
            $this->rooms[$roomName][] = [

                'connection' => $from
            ];
        } else {
            $room[] = $from;
        }

        return [
            'name' => $roomName,
            'connections' => [

            ]
        ];
    }
}
