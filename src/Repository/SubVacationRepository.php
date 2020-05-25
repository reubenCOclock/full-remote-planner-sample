<?php

namespace App\Repository;

use App\Entity\SubVacation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubVacation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubVacation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubVacation[]    findAll()
 * @method SubVacation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubVacationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubVacation::class);
    }

    // /**
    //  * @return SubVacation[] Returns an array of SubVacation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubVacation
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
