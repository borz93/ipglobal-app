<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\StockManager;

#[AsCommand(
    name: 'app:reset-stock',
    description: 'Reset all product stocks to default value (10)'
)]
class ResetStockCommand extends Command
{
    public function __construct(
        private StockManager $stockManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('Resetting stock for all products to the default value (10)...');
            $this->stockManager->resetAllStocks();
            $output->writeln('<info>Successfully reset all stock values to 10.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error resetting stocks: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}