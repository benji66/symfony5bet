<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\ApuestaTipo;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
                   

        $row = new ApuestaTipo();
        $row->setNombre('1P');
        $row->setDescripcion('Descripcion Apuesta 1P');
        $manager->persist($row);


        $row->setNombre('2P');
        $row->setDescripcion('Descripcion Apuesta 2P');
        $manager->persist($row);

        $row->setNombre('3P');
        $row->setDescripcion('Descripcion Apuesta 3P');
        $manager->persist($row);                 

        $manager->flush();

    }
}
