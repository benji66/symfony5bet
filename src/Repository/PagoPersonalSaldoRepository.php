<?php

namespace App\Repository;

use App\Entity\PagoPersonalSaldo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PagoPersonalSaldo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PagoPersonalSaldo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PagoPersonalSaldo[]    findAll()
 * @method PagoPersonalSaldo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoPersonalSaldoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagoPersonalSaldo::class);
    }

    // /**
    //  * @return PagoPersonalSaldo[] Returns an array of PagoPersonalSaldo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PagoPersonalSaldo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
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
            ->andWhere('a.updatedAt > :fecha')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->orderBy('a.updatedAt', 'DESC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}
