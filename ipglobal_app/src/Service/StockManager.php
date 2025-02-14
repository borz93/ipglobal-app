<?php

namespace App\Service;

use App\Entity\Stock;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\PessimisticLockException;
use Psr\Log\LoggerInterface;

class StockManager
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 100;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockRepository $stockRepository,
        private LoggerInterface $logger
    ) {}

    /**
     * Decrements stock for a product with concurrency control.
     *
     * @param int $productId The ID of the product.
     * @return bool True if stock was decremented, false if out of stock.
     * @throws \RuntimeException If the operation fails after retries.
     */
    public function decrementStock(int $productId): bool
    {
        $retryCount = 0;

        do {
            $this->entityManager->beginTransaction();

            try {
                $this->logger->info("Attempting to decrement stock for product ID: $productId (Retry: $retryCount)");

                // Attempt to acquire pessimistic lock
                $stock = $this->stockRepository->findWithLock($productId);

                // If stock does not exist, create it with default quantity
                if (!$stock) {
                    $this->logger->info("Creating new stock entry for product ID: $productId");
                    $stock = new Stock($productId, 10);
                    $this->entityManager->persist($stock);
                    $this->entityManager->flush();
                }

                // Check stock availability
                if ($stock->getQuantity() > 0) {
                    $stock->setQuantity($stock->getQuantity() - 1);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                    $this->logger->info("Stock decremented for product ID: $productId");
                    return true;
                }

                $this->entityManager->commit();
                $this->logger->info("Out of stock for product ID: $productId");
                return false;

            } catch (\Exception $e) {
                $this->entityManager->rollback();
                $retryCount++;

                if ($retryCount >= self::MAX_RETRIES) {
                    $this->logger->error("Failed to decrement stock for product ID: $productId after retries - " . $e->getMessage());
                    throw new \RuntimeException("Failed to decrement stock for product ID: $productId", 0, $e);
                }

                usleep(self::RETRY_DELAY_MS * 1000 * pow(2, $retryCount)); // Exponential backoff
            }
        } while ($retryCount < self::MAX_RETRIES);

        return false;
    }

    /**
     * Resets the stock for all products to the default value (10).
     */
    public function resetAllStocks(): void
    {
        $this->logger->info("Resetting stock for all products to default value (10)");

        $stocks = $this->entityManager->getRepository(Stock::class)->findAll();

        foreach ($stocks as $stock) {
            $stock->setQuantity(10);
        }

        $this->entityManager->flush();

        $this->logger->info("Stock reset completed");
    }
}