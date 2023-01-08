<?php

namespace App\Command;

use App\WebSocket\ChatHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:server:chat', 'Start chat server')]
class WebsocketServerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 8001;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatHandler()
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
