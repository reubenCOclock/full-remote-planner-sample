<?php

namespace App\Repository;

use App\Entity\WorkingDaysTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkingDaysTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkingDaysTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkingDaysTest[]    findAll()
 * @method WorkingDaysTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkingDaysTestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkingDaysTest::class);
    }

    // /**
    //  * @return WorkingDaysTest[] Returns an array of WorkingDaysTest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkingDaysTest
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
