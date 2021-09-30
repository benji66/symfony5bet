<?php

namespace App\Repository;

use App\Entity\PerfilBanco;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PerfilBanco|null find($id, $lockMode = null, $lockVersion = null)
 * @method PerfilBanco|null findOneBy(array $criteria, array $orderBy = null)
 * @method PerfilBanco[]    findAll()
 * @method PerfilBanco[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerfilBancoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerfilBanco::class);
    }

    // /**
    //  * @return PerfilBanco[] Returns an array of PerfilBanco objects
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
    public function findOneBySomeField($value): ?PerfilBanco
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

       public function findByPerfil($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.perfil = :val')
            ->andWhere('p.activo = :activo')
            ->setParameter('val', $value)
            ->setParameter('activo', true)
            
            ->getQuery()
            ->getResult()
        ;
    }
}
