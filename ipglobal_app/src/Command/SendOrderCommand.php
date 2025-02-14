<?php

namespace App\Command;

use App\Message\OrderMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:send-order',
    description: 'Sends an order to RabbitMQ for processing',
)]
class SendOrderCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'product-id',
                InputArgument::REQUIRED,
                'ID of the product to order (positive integer)'
            )
            ->setHelp('This command sends an order to the message queue. Example: %command.full_name% 2');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productId = $input->getArgument('product-id');

        // Validate that the product ID is a positive integer
        if (!filter_var($productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $output->writeln('<error>Invalid product ID. Must be a positive integer.</error>');
            return Command::FAILURE;
        }

        $productId = (int)$productId;

        // Generate a unique order ID (UUID as a string)
        $orderId = Uuid::v4()->toRfc4122();
        // Simulate a user ID
        $userId = random_int(1, 10000);
        $createdAt = new \DateTimeImmutable();

        // Create and dispatch the message
        $this->messageBus->dispatch(new OrderMessage(
            $orderId,
            $userId,
            $createdAt,
            $productId
        ));

        $output->writeln(sprintf(
            '<info>Successfully sent order:</info> Order ID: %s, Product ID: %d, Created at: %s',
            $orderId,
            $productId,
            $createdAt->format('Y-m-d H:i:s')
        ));

        return Command::SUCCESS;
    }
}