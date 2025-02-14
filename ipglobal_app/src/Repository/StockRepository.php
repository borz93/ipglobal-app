<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\PessimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * Finds a Stock entity by productId with pessimistic write lock.
     *
     * This method ensures that no other process can modify the stock
     * while the current process is working with it.
     *
     * @param int $productId The ID of the product.
     * @return Stock|null The stock entity, or null if not found.
     * @throws OptimisticLockException If an optimistic lock error occurs.
     * @throws PessimisticLockException If a pessimistic lock error occurs.
     */
    public function findWithLock(int $productId): ?Stock
    {
        return $this->getEntityManager()->find(
            Stock::class,
            $productId,
            LockMode::PESSIMISTIC_WRITE
        );
    }
}