<?php

namespace App\Command;

use App\Repository\ItemTypeRepository;
use App\Repository\MessageRepository;
use App\Repository\StoreItemRepository;
use App\Utils\StoreItemType;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:store:auto-change', 'Randomly change all auto item in store')]
class StoreAutoChangeCommand extends Command
{
    public function __construct(
        private readonly ItemTypeRepository $itemTypeRepository,
        private readonly StoreItemRepository $storeItemRepository,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $items = $this->itemTypeRepository->findAll();
        $limit = count($items);

        $changeSuccess = 0;

        $storeAutoItems = $this->storeItemRepository->findBy([ 'type' => StoreItemType::AUTO->value ]);
        foreach ($storeAutoItems as $item) {
            $rand = random_int(0, $limit - 1);
            try {
                $item->setItem($items[$rand]);
                $changeSuccess++;
            } catch (Exception $ex) {
                $io->error($ex->getMessage());
            }

            $this->storeItemRepository->save($item);
        }
        $this->storeItemRepository->flush();

        $io->success(sprintf("On %d auto items, %d have been changed !", count($storeAutoItems), $changeSuccess));

        return Command::SUCCESS;
    }
}
