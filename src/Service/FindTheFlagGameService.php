<?php

namespace App\Service;

use App\Entity\GameConnection;
use App\Entity\GameRoom;
use App\Entity\GameRoomMember;
use App\Entity\Score;
use App\Entity\User;
use App\Exception\LevelNotFoundApiException;
use App\Repository\LevelRepository;
use App\Repository\ScoreRepository;
use App\Utils\GameRoomVisibility;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class FindTheFlagGameService extends WebSocketService
{
    private GameRoomService $gameRoomService;

    public function __construct(
        private readonly UserService       $userService,
        private readonly LevelRepository   $levelRepository,
        private readonly ScoreRepository   $scoreRepository,
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
            $user = $this->userService->getById($data['user_id']);
            $level = $this->levelRepository->findOneBy(['id' => $data['level_id']]);
            if (is_null($level)) throw new LevelNotFoundApiException();

            $room = $this->gameRoomService->getRoomByName($roomName);
            if (is_null($room)) {
                $room = $this->gameRoomService->createFindTheFlagRoom($roomName, $level, GameRoomVisibility::PUBLIC);
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

    public function extractConnection(ConnectionInterface $from): void
    {
        $this->gameRoomService->extract($from);
        foreach ($this->gameRoomService->getRooms() as $room) {
            if (count($room->getConnections()) === 0) {
                $this->gameRoomService->removeRoomByName($room->getName());
            }
        }
    }

    public function leaveRoom(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['room_name'];
        $room = $this->gameRoomService
            ->getRoomByName($roomName);

        if (is_null($room)) return [
            'exception' => [
                'code' => 404,
                'message' => 'Room not Found'
            ]
        ];

        $room->removeConnectionByConnectionInterface($from);

        try {

            if (count($room->getConnections()) <= 0) {
                $this->gameRoomService->removeRoomByName($roomName);
            } else {
                $user = $this->userService->getById($data['user_id']);
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
            $user = $this->userService->getById($data['user_id']);
            $level = $this->levelRepository->findOneBy(['id' => $data['level_id']]);
            if (is_null($level)) throw new LevelNotFoundApiException();

            $room = $this->gameRoomService->createFindTheFlagRoom(null, $level, GameRoomVisibility::PRIVATE);

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

    public function guessCountry(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['room_name'];
        $room = $this->gameRoomService
            ->getRoomByName($roomName);

        if (is_null($room)) return [
            'exception' => [
                'code' => 404,
                'message' => 'Room not Found'
            ]
        ];

        try {
            $isCorrect = $room->guess($data['user_id'], $data['response']['iso'], $data['response']['country_name']);

            return [
                'event' => '@CountryGuess',
                'room' => $room->getName(),
                'is_correct' => $isCorrect
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

    public function finishGame(ConnectionInterface $from, array $data): array
    {
        $roomName = $data['room_name'];
        $room = $this->gameRoomService
            ->getRoomByName($roomName);

        if (is_null($room)) return [
            'exception' => [
                'code' => 404,
                'message' => 'Room not Found'
            ]
        ];

        try {
            $time = $data['game_time'];
            if (is_null($time)) throw new \HttpException("game_time is missing", 401);

            $userGuess = $room->getUserGuessByUserId($data['user_id']);

            $score = $userGuess->getScore();

            $user = $this->userService->getById($data['user_id']);
            $oldCoins = $user->getCoins();
            $oldXp = $user->getXp();

            $s = new Score();
            $s->setScore($score);
            $s->setUser($user);
            $s->setLevel($room->getLevel());
            $s->setTime($time);
            $s->setCreatedAt(new \DateTimeImmutable());

            $this->scoreRepository->save($s);

            $coins = round($score / 20);
            $xp = round($score / 100 * 2);

            $user->addScore($s);
            $user->setCoins($oldCoins + $coins);
            $user->setXp($oldXp + $xp);
            $this->userService->save($user, true);

            return [
                'event' => '@GameFinished',
                'room' => $room->getName(),
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                    'isVerified' => $user->isVerified(),
                    'img' => $user->getImg(),
                    'xp' => $user->getXp(),
                    'coins' => $user->getCoins(),
                    'before' => [
                        'xp' => $oldXp,
                        'coins' => $oldCoins,
                    ]
                ],
                'game' => [
                    'score' => $score,
                    'rewards' => [
                        'xp' => $xp,
                        'coins' => $coins
                    ],
                    'answers' => array_map(fn($a) => [
                        'iso' => $a->iso,
                        'correct_answer' => $a->correctAnswer,
                        'user_answer' => $a->userAnswer,
                        'is_correct' => $a->isCorrect()
                    ], array_values($userGuess->getAnswers()))
                ]
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
