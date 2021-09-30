<?php

namespace App\Repository;

use App\Entity\Carrera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Carrera|null find($id, $lockMode = null, $lockVersion = null)
 * @method Carrera|null findOneBy(array $criteria, array $orderBy = null)
 * @method Carrera[]    findAll()
 * @method Carrera[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarreraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carrera::class);
    }

    // /**
    //  * @return Carrera[] Returns an array of Carrera objects
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
    public function findOneBySomeField($value): ?Carrera
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

        public function findByGerenciaWeeks($gerencia_id)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.gerencia = :gerencia_id')            
            ->andWhere('a.fecha > :fecha')
            ->setParameter('gerencia_id', $gerencia_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'- 2 week' )))
            ->orderBy('a.fecha', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findByStatus($parametros)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.gerencia = :gerencia_id')
            ->andWhere('a.status = :status')            
            ->andWhere('a.fecha > :fecha')
            ->setParameter('gerencia_id', $parametros['gerencia'])
            ->setParameter('status', $parametros['status'])
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'- 2 week' )))
            ->orderBy('a.fecha', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
   
    }
}
