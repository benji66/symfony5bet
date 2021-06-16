<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\Pais;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
                   

        $row = new Pais();
        $row->setNombre('India');
        $row->setPoblacion(1409902000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('China');
        $row->setPoblacion(1403426000);
        $manager->persist($row);        

        $row = new Pais();
        $row->setNombre('Estados Unidos');
        $row->setPoblacion(331800000);
        $manager->persist($row);


        $row = new Pais();
        $row->setNombre('Indonesia');
        $row->setPoblacion(271629000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Pakistan');
        $row->setPoblacion(224654000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Nigeria');
        $row->setPoblacion(219743000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Brasil');
        $row->setPoblacion(211420000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Banglades');
        $row->setPoblacion(181781000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Rusia');
        $row->setPoblacion(146712000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Mexico');
        $row->setPoblacion(127792000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Japon');
        $row->setPoblacion(126045000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Filipinas');
        $row->setPoblacion(108772000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Egipto');
        $row->setPoblacion(101000000);
        $manager->persist($row);     

        $row = new Pais();
        $row->setNombre('Etiopia');
        $row->setPoblacion(97591000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Vietnam');
        $row->setPoblacion(89561000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Republica del Congo');
        $row->setPoblacion(83914000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Iran');
        $row->setPoblacion(83914000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Turquia');
        $row->setPoblacion(83752000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Alemania');
        $row->setPoblacion(83421000);
        $manager->persist($row);

        $row = new Pais();
        $row->setNombre('Tailandia');
        $row->setPoblacion(68232000);
        $manager->persist($row); 
                                                    

        $manager->flush();

    }
}
