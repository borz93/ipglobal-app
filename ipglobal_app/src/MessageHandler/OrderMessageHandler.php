<?php

namespace App\MessageHandler;

use App\Entity\Order;
use App\Message\OrderMessage;
use App\Repository\OrderRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private StockManager $stockManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Handles the processing of an order message.
     *
     * @param OrderMessage $message The order message to process.
     * @throws \DateMalformedStringException
     */
    public function __invoke(OrderMessage $message): void
    {
        $orderId = $message->getOrderId();
        $productId = $message->getProductId();

        $this->logger->info('Processing order: {orderId}', ['orderId' => $orderId]);

        try {
            // Simulate order processing delay
            sleep(rand(1, 5));

            // Check stock availability and decrement stock if available
            $isApproved = $this->stockManager->decrementStock($productId);

            // Find or create the order
            $order = $this->orderRepository->findOrCreate(
                $orderId,
                $message->getUserId(),
                $message->getCreatedAt()
            );

            // Update order status and processed timestamp
            $order->setStatus($isApproved ? Order::STATUS_APPROVED : Order::STATUS_REJECTED);
            $order->setProcessedAt(new \DateTime());

            // Persist and flush the order to the database
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $this->logger->info('Order processed: {orderId}, Status = {status}', [
                'orderId' => $orderId,
                'status' => $order->getStatus(),
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error processing order {orderId}: {error}', [
                'orderId' => $orderId,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception for automatic retries
            throw $e;
        }
    }
}