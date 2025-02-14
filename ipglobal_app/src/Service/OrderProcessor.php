<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class OrderProcessor
{
    private StockManager $stockManager;
    private LoggerInterface $logger;

    public function __construct(StockManager $stockManager, LoggerInterface $logger)
    {
        $this->stockManager = $stockManager;
        $this->logger = $logger;
    }

    /**
     * Checks and decrements stock for a product.
     *
     * @param int $productId The ID of the product.
     * @return bool True if stock is available and decremented, false otherwise.
     */
    public function checkStockAvailability(int $productId): bool
    {
        try {
            $this->logger->info("Checking stock availability for product ID: $productId");
            return $this->stockManager->decrementStock($productId);
        } catch (\RuntimeException $e) {
            $this->logger->error("Error processing stock for product ID: $productId - " . $e->getMessage());
            return false;
        }
    }
}