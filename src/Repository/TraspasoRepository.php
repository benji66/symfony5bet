<?php

namespace App\Repository;

use App\Entity\Traspaso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Traspaso|null find($id, $lockMode = null, $lockVersion = null)
 * @method Traspaso|null findOneBy(array $criteria, array $orderBy = null)
 * @method Traspaso[]    findAll()
 * @method Traspaso[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraspasoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Traspaso::class);
    }

    // /**
    //  * @return Traspaso[] Returns an array of Traspaso objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Traspaso
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findById15Dias($perfil_id)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.descuento = :perfil_id')
            ->orWhere('a.abono = :perfil_id')
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
