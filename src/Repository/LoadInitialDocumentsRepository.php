<?php

namespace App\Repository;

use App\Entity\LoadInitialDocuments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LoadInitialDocuments|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoadInitialDocuments|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoadInitialDocuments[]    findAll()
 * @method LoadInitialDocuments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoadInitialDocumentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LoadInitialDocuments::class);
    }

    // /**
    //  * @return LoadInitialDocuments[] Returns an array of LoadInitialDocuments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LoadInitialDocuments
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
