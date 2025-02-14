<?php

namespace App\Tests\Service;

use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StockManagerTest extends TestCase
{
    public function testDecrementStockCreatesNewStockIfNotExists()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(StockRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Simulate no existing stock
        $repo->method('findWithLock')->willReturn(null);

        // Expect persist and flush calls
        $em->expects($this->once())->method('persist');
        $em->expects($this->exactly(2))->method('flush');

        $stockManager = new StockManager($em, $repo, $logger);
        $result = $stockManager->decrementStock(1);

        $this->assertTrue($result);
    }
}