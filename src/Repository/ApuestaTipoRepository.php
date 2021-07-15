<?php

namespace App\Repository;

use App\Entity\ApuestaTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApuestaTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApuestaTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApuestaTipo[]    findAll()
 * @method ApuestaTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApuestaTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApuestaTipo::class);
    }

    // /**
    //  * @return ApuestaTipo[] Returns an array of ApuestaTipo objects
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
    public function findOneBySomeField($value): ?ApuestaTipo
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
