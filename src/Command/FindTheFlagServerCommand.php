<?php

namespace App\Command;

use App\Repository\LevelRepository;
use App\Repository\ScoreRepository;
use App\Service\UserService;
use App\WebSocket\FindTheFlagGameHandler;
use Psr\Log\LoggerInterface;
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
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
        private readonly LevelRepository $levelRepository,
        private readonly ScoreRepository $scoreRepository,
    )
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
                    new FindTheFlagGameHandler(
                        $this->logger,
                        $this->userService,
                        $this->levelRepository,
                        $this->scoreRepository
                    )
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
