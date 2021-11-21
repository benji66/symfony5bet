<?php

namespace App\Repository;

use App\Entity\Banca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Banca|null find($id, $lockMode = null, $lockVersion = null)
 * @method Banca|null findOneBy(array $criteria, array $orderBy = null)
 * @method Banca[]    findAll()
 * @method Banca[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BancaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banca::class);
    }

    // /**
    //  * @return Banca[] Returns an array of Banca objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Banca
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByUsuario($parametros)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.usuario = :perfil_id')
            ->andWhere('b.procesado_at is null')            
            ->setParameter('perfil_id', $parametros['perfil_id'])
            ->orderBy('b.juega', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUsuarioJuega($parametros)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.usuario = :perfil_id')
            ->andWhere('b.juega = :juega')
            ->andWhere('b.procesado_at is null')            
            ->setParameter('perfil_id', $parametros['perfil_id'])
            ->setParameter('juega', $parametros['juega'])
            ->orderBy('b.monto', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }

}
