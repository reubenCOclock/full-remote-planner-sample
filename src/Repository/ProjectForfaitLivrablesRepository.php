<?php

namespace App\Repository;

use App\Entity\ProjectForfaitLivrables;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectForfaitLivrables|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectForfaitLivrables|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectForfaitLivrables[]    findAll()
 * @method ProjectForfaitLivrables[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectForfaitLivrablesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectForfaitLivrables::class);
    }

    // /**
    //  * @return ProjectForfaitLivrables[] Returns an array of ProjectForfaitLivrables objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjectForfaitLivrables
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
