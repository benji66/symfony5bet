<?php

namespace App\Repository;

use App\Entity\AdjuntoPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AdjuntoPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdjuntoPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdjuntoPago[]    findAll()
 * @method AdjuntoPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdjuntoPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdjuntoPago::class);
    }

    // /**
    //  * @return AdjuntoPago[] Returns an array of AdjuntoPago objects
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
    public function findOneBySomeField($value): ?AdjuntoPago
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
            ->andWhere('a.validado is not null')
            ->andWhere('a.updatedAt > :fecha')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}
