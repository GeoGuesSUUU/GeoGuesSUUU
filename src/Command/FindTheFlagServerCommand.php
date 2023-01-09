<?php

namespace App\Command;

use App\Repository\MessageRepository;
use App\Service\UserService;
use App\WebSocket\ChatHandler;
use App\WebSocket\FindTheFlagGameHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:server:find-the-flag', 'Start "Find The Flag" Game Server')]
class FindTheFlagServerCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 9000;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new FindTheFlagGameHandler()
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
