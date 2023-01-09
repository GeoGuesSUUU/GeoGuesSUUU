<?php

namespace App\Command;

use App\Repository\MessageRepository;
use App\Service\UserService;
use App\WebSocket\ChatHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:server:chat', 'Start chat server')]
class ChatServerCommand extends Command
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly UserService       $userService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 8001;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatHandler(
                        $this->messageRepository,
                        $this->userService
                    )
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
