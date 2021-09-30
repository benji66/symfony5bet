<?php

namespace App\Repository;

use App\Entity\ApuestaPropuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApuestaPropuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApuestaPropuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApuestaPropuesta[]    findAll()
 * @method ApuestaPropuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApuestaPropuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApuestaPropuesta::class);
    }

    // /**
    //  * @return ApuestaPropuesta[] Returns an array of ApuestaPropuesta objects
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
    public function findOneBySomeField($value): ?ApuestaPropuesta
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
            ->andWhere('a.jugador = :perfil_id')            
            ->andWhere('a.updatedAt > :fecha')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-1 week' )))
            ->orderBy('a.updatedAt', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }
}
