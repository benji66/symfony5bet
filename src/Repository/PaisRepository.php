<?php

namespace App\Repository;

use App\Entity\Pais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pais|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pais|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pais[]    findAll()
 * @method Pais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pais::class);
    }

    // /**
    //  * @return Pais[] Returns an array of Pais objects
    //  */


    public function buscarPorNombre($value)
    {
        
        /* 
        select p1.nombre, p1.poblacion, 
        ((p1.poblacion/(select SUM(p2.poblacion) as total from pais p2 where p2.nombre like '%dia%' ))*100) as porcentaje
         from pais p1 
         where p1.nombre like '%dia%' 

         order by nombre ASC 
        */

        $sub = $this->totalPorNombre($value);
        $total = $sub['totalPoblacion'];

        $qb=  $this->createQueryBuilder('p')
        ->select( 'p.nombre', 'p.poblacion' )
        ->addSelect(sprintf(
                '((p.poblacion/(%1s))*100) AS %2s',
                //$sub->getQuery()->getDQL(),
                $total,
                'porcentaje'
            ))

            ->andWhere('p.nombre like :val')
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('p.nombre', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getArrayResult()        
        ;

        return $qb; 
    }

    public function totalPorNombre($value)
    {
        return $this->createQueryBuilder('p')
        ->select( 'SUM(p.poblacion) as totalPoblacion' )
           // ->andWhere('p.nombre like :val')
            //->setParameter('val', '%'.$value.'%')                    
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }






    
}
