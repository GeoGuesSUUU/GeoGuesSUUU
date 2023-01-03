<?php

namespace App\Command;

use App\Entity\Country;
use App\Service\CountryService;
use App\Utils\CountriesISO;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:country:init', 'Initialize many countries (default: 20)')]
class CountriesInitCommand extends Command
{

    public function __construct(
        private readonly CountryService $countryService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('number', InputArgument::OPTIONAL, 'The number of countries you want to initialize', 20);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $number = $input->getArgument('number') ?? 20;
        $limit = count(CountriesISO::keys());
        if ($number > $limit) {
            $io->error(sprintf('You cannot initialize more than %d countries', $limit));
        }

        for ($i = 0; $i < $number; $i++) {
            $rand = random_int(0, $limit - 1);
            $iso = CountriesISO::countriesCases()[$rand];

            $country = new Country();
            $country->setName($iso->value);
            $country->setCode($iso->name);
            $country->setContinent('Unknown');
            $country->setInitLife(random_int(5, 50) * 100000);
            $country->setInitLife(random_int(25, 300) * 10000);
            $country->setShieldMax(10000);

            $this->countryService->create($country);
        }

        $this->countryService->flush();

        $io->success(sprintf('"%d" countries have been initialize.', $number));

        return Command::SUCCESS;
    }
}
