<?php

namespace App\Command;

use App\Repository\MessageRepository;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:message:delete24', 'Delete all messages with a publication date older than 24 hours')]
class DeleteOlderMessagesCommand extends Command
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->messageRepository->removeAllOlderMessages(24);

            $io->success("All older messages have been removed !");

            return Command::SUCCESS;
        } catch (Exception $ex) {
            $io->error($ex->getMessage());

            return Command::FAILURE;
        }
    }
}
