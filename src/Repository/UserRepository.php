<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */ 

    public function findUserByEmail($email){
        $qb=$this->createQueryBuilder('u')
        ->where('u.email =:email')
        ->setParameter('email',$email)
        ->getQuery();
        return $qb->setMaxResults(1)->getOneOrNullResult();
    } 

    public function findUserByRoleAdmin($roleTitle){
        $qb=$this->createQueryBuilder('u')
        ->join('u.role','r')
        ->where('u.roleTitle=:roleTitle') 
        ->setParameter('roleTitle',$roleTitle)
        ->getQuery(); 
        return $qb->getResult()->execute();
    } 



    public function filterUsersByRoleConsultant(){
        $qb=$this->createQueryBuilder('u')
        ->join('u.role','r')
        ->where('r.roleTitle=:ROLE_CONSULTANT')
        ->setParameter('ROLE_CONSULTANT','ROLE_CONSULTANT')
        ->orderBy('u.createdAt','ASC')
        ->orderBy('u.isEmployed','ASC')
        ->getQuery();
        return $qb->getResult();
        
    } 

    public function filterUsersByRoleClient(){
        $qb=$this->createQueryBuilder('u')
        ->join('u.role','r')
        ->where('r.roleTitle=:ROLE_CLIENT')
        ->setParameter('ROLE_CLIENT','ROLE_CLIENT')
        ->orderBy('u.createdAt','ASC')
        ->getQuery();
        return $qb->getResult();
    } 

    public function filterUsersByRoleRh(){
        $qb=$this->createQueryBuilder('u')
        ->join('u.role','r')
        ->where('r.roleTitle=:ROLE_RH')
        ->setParameter('ROLE_RH','ROLE_RH')
        ->getQuery();
        return $qb->getResult();
    } 

    public function filterDesactivatedRoleRh(){
        $qb=$this->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle!=:ROLE_RH')->setParameter('ROLE_RH','ROLE_RH')->andWhere('u.isEmployed=:false')->setParameter('false','false')->getQuery();
        return $qb->getResult();
    } 

    public function filterUsersByRHAndConsultant(){
        $qb=$this->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle!=:role_client')->setParameter('role_client','ROLE_CLIENT')->andWhere('r.roleTitle!=:role_admin')->setParameter('role_admin','ROLE_ADMIN')->orderBy('u.isEmployed','DESC')->addOrderBy('u.createdAt','DESC')->getQuery();
        return $qb->getResult();
    } 

    public function getConsultantsByProject($projectId){
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM project_user pu
            WHERE pu.project_id = :projectId
            
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['projectId' => $projectId]);
    
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    } 

    public function findAllActiveConsultants(){
        $qb=$this->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle=:role_consultant')->setParameter('role_consultant','ROLE_CONSULTANT')->andWhere('u.isEmployed=:true')->setParameter('true',true)->getQuery();
        return $qb->getResult();
    }

    public function associateConsultantToProject($id){
        $qb=$this->createQueryBuilder('c')->where('c.id=:id')->setParameter('id',$id)->getQuery();
        return $qb->getResult();
    }

    public function associateClientToProject($id){
        $qb=$this->createQueryBuilder('c')->where('c.id=:id')->setParameter('id',$id)->getQuery();
        return $qb->getResult();
    }



    public function associateBillToConsultant($firstName){
        $qb=$this->createQueryBuilder('c')->where('c.firstname=:firstname')->setParameter('firstname',$firstName)->getQuery();
        return $qb->getResult();
    }

    

   
}
