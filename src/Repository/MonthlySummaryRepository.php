<?php

namespace App\Repository;

use App\Entity\MonthlySummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MonthlySummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlySummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlySummary[]    findAll()
 * @method MonthlySummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlySummaryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MonthlySummary::class);
    }

    // /**
    //  * @return MonthlySummary[] Returns an array of MonthlySummary objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MonthlySummary
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getRecapsByUserAndMonth($id,$month,$year){
        $qb=$this->createQueryBuilder('ms')->join('ms.consultant','c')->where('c.id=:id')->setParameter('id',$id)->andWhere('c.isEmployed=:true')->setParameter('true',true)->andWhere('ms.month=:month')->setParameter('month',$month)->andWhere('ms.year=:year')->setParameter('year',$year)->getQuery();
        return $qb->getResult();
    }

    public function sortAllRecapsByUserMonthAndYear($month,$year){
        $qb=$this->createQueryBuilder('ms')->join('ms.consultant','c')->where('ms.year=:year')->setParameter('year',$year)->andWhere('ms.month=:month')->setParameter('month',$month)->orderBy('c.firstname')->getQuery();
        return $qb->getResult();
    }
}
