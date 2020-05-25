<?php

namespace App\Repository;

use App\Entity\ConsultantProjectPricesRegie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ConsultantProjectPricesRegie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsultantProjectPricesRegie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsultantProjectPricesRegie[]    findAll()
 * @method ConsultantProjectPricesRegie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsultantProjectPricesRegieRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ConsultantProjectPricesRegie::class);
    }

    // /**
    //  * @return ConsultantProjectPricesRegie[] Returns an array of ConsultantProjectPricesRegie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConsultantProjectPricesRegie
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
