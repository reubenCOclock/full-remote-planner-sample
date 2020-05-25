<?php

namespace App\Repository;

use App\Entity\MyDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyDocument[]    findAll()
 * @method MyDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyDocumentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyDocument::class);
    }

    // /**
    //  * @return MyDocument[] Returns an array of MyDocument objects
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
    public function findOneBySomeField($value): ?MyDocument
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUserByDocuments($id){
        
            $qb=$this->createQueryBuilder('d')
            ->join('d.client','c')
            ->where('c.id=:id')
            ->setParameter('id',$id)
            ->orderBy('d.year','DESC')
            ->addOrderBy('d.month','DESC')
            ->getQuery();
            return $qb->getResult();
        } 
        
    
    public function getFactureDocumentsByMonthAndYear($month,$year){
        $qb=$this->createQueryBuilder('d')->where('d.month=:month')->setParameter('month',$month)->andWhere('d.category=:category')->setParameter('category','facture')->andWhere('d.year=:year OR d.year=:null')->setParameter('year',$year)->setParameter('null',null)->getQuery();
        return $qb->getResult();
    }
    } 

