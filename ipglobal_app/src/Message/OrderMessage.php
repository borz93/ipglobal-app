<?php

namespace App\Message;

use DateTimeInterface;

/**
 * Represents a message for processing an order.
 */
class OrderMessage
{
    private string $orderId;
    private int $userId;
    private DateTimeInterface $createdAt;
    private int $productId;

    /**
     * OrderMessage constructor.
     *
     * @param string $orderId     The unique ID of the order (UUID).
     * @param int $userId         The ID of the user placing the order.
     * @param DateTimeInterface $createdAt The timestamp when the order was created.
     * @param int $productId      The ID of the product being ordered.
     */
    public function __construct(
        string $orderId,
        int $userId,
        DateTimeInterface $createdAt,
        int $productId
    ) {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->createdAt = $createdAt;
        $this->productId = $productId;
    }

    /**
     * Gets the order ID.
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Gets the user ID.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Gets the creation timestamp of the order.
     *
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Gets the product ID.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }
}