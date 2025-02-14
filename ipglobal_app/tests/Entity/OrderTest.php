<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testOrderStatusTransitions()
    {
        $order = new Order();
        $order->setStatus(Order::STATUS_APPROVED);
        $order->setProcessedAt(new \DateTime());

        $this->assertEquals(Order::STATUS_APPROVED, $order->getStatus());
        $this->assertNotNull($order->getProcessedAt());
    }

    public function testOrderDefaultsToPendingStatus()
    {
        $order = new Order();
        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());
    }
}