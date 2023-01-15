<?php

namespace App\Service;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Exception;
use SplObjectStorage;

class ChatService extends WebSocketService
{

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly UserService       $userService,
        SplObjectStorage                   $connections
    )
    {
        parent::__construct($connections);
    }

    public function addMessageToDB(array $data): array
    {
        $msg = new Message();
        try {
            $user = $this->userService->getById($data['user']['id']);

            $msg->setUser($user);
            $msg->setContent($data['content']);
            $msg->setColor($data['user']['color']);
            $msg->setPublishAt(new \DateTimeImmutable());
            $this->messageRepository->save($msg, true);
            return [
                'event' => '@Message',
                'response' => [
                    'user' => [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'color' => $msg->getColor(),
                        'isAdmin' => in_array('ROLE_ADMIN', $msg->getUser()->getRoles()),
                        'isVerified' => $msg->getUser()->isVerified()
                    ],
                    'content' => $msg->getContent(),
                    'publishAt' => $msg->getPublishAt()
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

    /**
     * @return array
     */
    public function getMessages(): array
    {

        return [
            'event' => '@Messages',
            'response' => array_map(fn($msg) => [
                'user' => [
                    'id' => $msg->getUser()->getId(),
                    'name' => $msg->getUser()->getName(),
                    'color' => $msg->getColor(),
                    'isAdmin' => in_array('ROLE_ADMIN', $msg->getUser()->getRoles()),
                    'isVerified' => $msg->getUser()->isVerified()
                ],
                'content' => $msg->getContent(),
                'publishAt' => $msg->getPublishAt()
            ], $this->messageRepository->findAll())
        ];
    }

    public function getCountConnection(): array
    {
        return [
            'event' => '@ConnectionCount',
            'response' => [
                'count' => $this->connections->count()
            ]
        ];
    }
}
