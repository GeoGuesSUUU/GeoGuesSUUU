<?php

namespace App\Command;

use App\Service\CountryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:shield:restore', 'Restore 20% of all countries shield')]
class ShieldRestoreCommand extends Command
{
    public function __construct(
        private readonly CountryService $countryService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $countries = $this->countryService->getAll();

        $countries = array_filter($countries, function ($country) {
            return $country->getUser() !== null && $country->getShield() < $country->getShieldMax();
        });

        foreach ($countries as $c) {
            $this->countryService->restoreShield($c, 20);
        }

        $this->countryService->flush();

        $io->success(sprintf('"%d" countries have been restored from 20 percent shield.', count($countries)));

        return Command::SUCCESS;
    }
}
