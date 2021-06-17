<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\Dependencia;


class UserFixtures extends Fixture
{

     private $passwordEncoder;

     public function __construct(UserPasswordEncoderInterface $passwordEncoder)
     {
         $this->passwordEncoder = $passwordEncoder;
     }


    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
         
		 $roles[] = 'ROLE_USER';
		 $roles[] = 'ROLE_ADMIN'; 
        

         $perfil = new Perfil();
         $perfil->setNombre('admin');
         $perfil->setApellido('admin');
         $perfil->setNickname('fran66');

         
         $user = new User();
         $user->setPerfil($perfil);
         $user->setEmail('admin@admin.com');
         $user->setPassword($this->passwordEncoder->encodePassword(
             $user,
             'admin'
         ));


        $user->setRoles($roles);
        $manager->persist($user);
        $manager->flush();

    }
}