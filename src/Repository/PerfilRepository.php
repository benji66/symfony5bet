<?php

namespace App\Repository;

use App\Entity\Perfil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Perfil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perfil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perfil[]    findAll()
 * @method Perfil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perfil::class);
    }

    // /**
    //  * @return Perfil[] Returns an array of Perfil objects
    //  */
    
    public function findByGerencia($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.gerencia = :val')
            ->andWhere('p.activo = true')
            ->setParameter('val', $value)
            ->orderBy('p.nickname', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Perfil
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
