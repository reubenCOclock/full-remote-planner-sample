<?php

namespace App\Repository;

use App\Entity\Vacation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vacation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vacation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vacation[]    findAll()
 * @method Vacation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vacation::class);
    }

    // /**
    //  * @return Vacation[] Returns an array of Vacation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vacation
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function sortAbsencesByUserAndEndDate($id){
        
        $qb=$this->createQueryBuilder('v')->join('v.consultant','c')->where('c.id=:id')->setParameter('id',$id)->orderBy('v.endDate','DESC')->getQuery();
        return $qb->getResult();
    } 

    public function getVacationsWithFutureStartDate($now){
        $qb=$this->createQueryBuilder('v')->where('v.startDate > :now')->setParameter('now',$now)->getQuery();
        return $qb->getResult();
    }

  
}
