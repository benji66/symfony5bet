<?php

namespace App\Repository;

use App\Entity\Nombre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Nombre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nombre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nombre[]    findAll()
 * @method Nombre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NombreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nombre::class);
    }

    // /**
    //  * @return Nombre[] Returns an array of Nombre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Nombre
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
