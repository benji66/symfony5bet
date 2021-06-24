<?php

namespace App\Repository;

use App\Entity\MetodoPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetodoPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetodoPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetodoPago[]    findAll()
 * @method MetodoPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetodoPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetodoPago::class);
    }

    // /**
    //  * @return MetodoPago[] Returns an array of MetodoPago objects
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
    public function findOneBySomeField($value): ?MetodoPago
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
