<?php

namespace App\Tests\Entity;

use App\Entity\Stock;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    public function testStockQuantityCannotBeNegative()
    {
        $this->expectException(\InvalidArgumentException::class);

        $stock = new Stock(1, 10);
        $stock->setQuantity(-1);
    }

    public function testStockInitialization()
    {
        $stock = new Stock(1, 15);
        $this->assertEquals(15, $stock->getQuantity());
    }
}