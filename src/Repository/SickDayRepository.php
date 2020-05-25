<?php

namespace App\Repository;

use App\Entity\SickDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SickDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method SickDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method SickDay[]    findAll()
 * @method SickDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SickDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SickDay::class);
    }

    // /**
    //  * @return SickDay[] Returns an array of SickDay objects
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
    public function findOneBySomeField($value): ?SickDay
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function sortAbsencesByUserAndEndDate($id){
        
        $qb=$this->createQueryBuilder('s')->join('s.consultant','c')->where('c.id=:id')->setParameter('id',$id)->orderBy('s.endDate','DESC')->getQuery();
        return $qb->getResult();
    }
}
