<?php

namespace App\Repository;

use App\Entity\Correccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Correccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Correccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Correccion[]    findAll()
 * @method Correccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorreccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Correccion::class);
    }

    // /**
    //  * @return Correccion[] Returns an array of Correccion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


}
