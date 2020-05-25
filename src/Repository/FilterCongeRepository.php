<?php

namespace App\Repository;

use App\Entity\FilterConge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FilterConge|null find($id, $lockMode = null, $lockVersion = null)
 * @method FilterConge|null findOneBy(array $criteria, array $orderBy = null)
 * @method FilterConge[]    findAll()
 * @method FilterConge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilterCongeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FilterConge::class);
    }

    // /**
    //  * @return FilterConge[] Returns an array of FilterConge objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FilterConge
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
