<?php

namespace App\Repository;

use App\Entity\Apuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Apuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Apuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Apuesta[]    findAll()
 * @method Apuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apuesta::class);
    }

    // /**
    //  * @return Apuesta[] Returns an array of Apuesta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Apuesta
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findById15Dias($perfil_id)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.perfil = :perfil_id')            
            //->andWhere('a.updatedAt > :fecha')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->orderBy('a.updatedAt', 'DESC')
            
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFecha($parametros)
    {
        return $this->createQueryBuilder('a')            
           
            ->innerJoin('a.carrera', 'c')
            ->andWhere('c.gerencia = :gerencia_id')           
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->setParameter('gerencia_id', $parametros['gerencia_id'])

            ->setParameter('fecha1', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->setParameter('fecha2', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->getQuery()
            ->getResult()
        ;
    }

}
