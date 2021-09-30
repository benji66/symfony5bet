<?php

namespace App\Repository;

use App\Entity\RetiroSaldo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RetiroSaldo|null find($id, $lockMode = null, $lockVersion = null)
 * @method RetiroSaldo|null findOneBy(array $criteria, array $orderBy = null)
 * @method RetiroSaldo[]    findAll()
 * @method RetiroSaldo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RetiroSaldoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RetiroSaldo::class);
    }

    // /**
    //  * @return RetiroSaldo[] Returns an array of RetiroSaldo objects
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
    public function findOneBySomeField($value): ?RetiroSaldo
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
            ->andWhere('a.perfil = :perfil_id')
            ->andWhere('a.validado is not null')
            ->andWhere('a.updatedAt > :fecha')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('fecha', date('Y-m-d', strtotime(date('Y-m-d').'-2 week' )))
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByMesGerenciaValidado(array $parametros)
    {
        
    //SELECT SUM(monto) FROM `adjunto_pago` WHERE validado = 1 and gerencia_id=1 and updated_at BETWEEN '2021-07-01 00:00:00' AND '2021-07-30 11:59:59'



        $month_start = strtotime('first day of '.$parametros['mes'], time());
        $month_end = strtotime('last day of '.$parametros['mes'], time());

      

        return $this->createQueryBuilder('a')
            ->select('SUM(a.monto) as suma')
            ->andWhere('a.gerencia = :gerencia_id')
            ->andWhere('a.validado = :validado')
            ->andWhere('a.updatedAt BETWEEN :fecha1 AND :fecha2')
            ->setParameter('gerencia_id', $parametros['gerencia_id'])
            ->setParameter('validado', $parametros['validado'])
            ->setParameter('fecha1', date('Y-m-d', $month_start))
            ->setParameter('fecha2', date('Y-m-d', $month_end))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }    
}
