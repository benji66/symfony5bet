<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\ApuestaTipo;
use App\Entity\Local;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $row = new Local();
        $row->setNombre('Hipodromo 1');
        $row->setDescripcion('Descripcion H1');
        $manager->persist($row); 

         $row = new Local();
        $row->setNombre('Hipodromo 2');
        $row->setDescripcion('Descripcion H2');
        $manager->persist($row); 

        $row = new Local();
        $row->setNombre('Hipodromo 3');
        $row->setDescripcion('Descripcion H3');
        $manager->persist($row); 
                   

        $row = new ApuestaTipo();
        $row->setNombre('1P');
        $row->setDescripcion('Descripcion Apuesta 1P');
        $manager->persist($row);

        $row = new ApuestaTipo();
        $row->setNombre('2P');
        $row->setDescripcion('Descripcion Apuesta 2P');
        $manager->persist($row);

         $row = new ApuestaTipo();
        $row->setNombre('3P');
        $row->setDescripcion('Descripcion Apuesta 3P');
        $manager->persist($row);

        $row = new ApuestaTipo();   
        $row->setNombre('4P');
        $row->setDescripcion('Descripcion Apuesta 4P');
        $manager->persist($row);                         

        $manager->flush();

    }
}
