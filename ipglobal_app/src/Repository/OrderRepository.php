<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Finds or creates an order.
     *
     * @param string $orderId   The UUID of the order.
     * @param int $userId       The ID of the user.
     * @param \DateTimeInterface $createdAt The creation timestamp.
     * @return Order
     */
    public function findOrCreate(string $orderId, int $userId, \DateTimeInterface $createdAt): Order
    {
        $order = $this->findOneBy(['orderId' => $orderId]);

        if (!$order) {
            $order = new Order();
            $order->setOrderId($orderId)
                ->setUserId($userId)
                ->setCreatedAt($createdAt);

            $this->getEntityManager()->persist($order);
        }

        return $order;
    }
}
