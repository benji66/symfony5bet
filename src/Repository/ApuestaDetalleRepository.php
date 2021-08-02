<?php

namespace App\Repository;

use App\Entity\ApuestaDetalle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApuestaDetalle|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApuestaDetalle|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApuestaDetalle[]    findAll()
 * @method ApuestaDetalle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApuestaDetalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApuestaDetalle::class);
    }

    // /**
    //  * @return ApuestaDetalle[] Returns an array of ApuestaDetalle objects
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
    public function findOneBySomeField($value): ?ApuestaDetalle
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
            //->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-1 week' )))
            ->orderBy('a.updatedAt', 'DESC')
            
            ->getQuery()
            ->getResult()
        ;
    }
}
