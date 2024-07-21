<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

        public function findBetweenDate($date1,$date2,$type,$owner): ?Array
        {
            return $this->createQueryBuilder('e')
            ->where('e.date >= :startDate')
                ->andWhere('e.date <= :endDate')
                ->andWhere('e.type = :type')
                ->andWhere('e.owner = :owner')
                ->setParameter('startDate', $date1->format('Y-m-d'))
                ->setParameter('endDate', $date2->format('Y-m-d'))
                ->setParameter('type', $type)
                ->setParameter('owner', $owner)
                ->orderBy('e.date', 'ASC')

                ->getQuery()
                ->getResult();
        }
}
