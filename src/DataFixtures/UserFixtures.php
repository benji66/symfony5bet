<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\Gerencia;
use App\Entity\MetodoPago;
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
       
         $gerencia = new Gerencia();
         $gerencia->setNombre('gerencia_demo');
	//-------USUARIO ADMIN

         $roles[] = 'ROLE_USER';
		 $roles[] = 'ROLE_ADMIN';         

          $perfil = new Perfil();
         $perfil->setNickname('fran66'); 
         $perfil->setGerencia($gerencia);  
         $perfil->setActivo(true);
         $perfil->setSaldoIlimitado(true);

         $user = new User();
         $user->addPerfil($perfil);           
         $user = new User();       
         $user->setEmail('admin@admin.com');
         $user->setNombre('admin');
         $user->setApellido('admin');
         $user->setPassword($this->passwordEncoder->encodePassword(
             $user,
             'admin'
         ));

        $user->setRoles($roles);
        $manager->persist($user);

//-------------GERENCIA
        $roles = null;
        $roles[] = 'ROLE_USER';
        $roles[] = 'ROLE_GERENCIA';       
         
         $metodo_pago = new MetodoPago();
         $metodo_pago->setNombre('pago movil mercantil');
         $metodo_pago->setDescripcion('datos cuenta etc');
         $metodo_pago->setActivo(true);
         $gerencia->addMetodoPago($metodo_pago);

         $metodo_pago = new MetodoPago();
         $metodo_pago->setNombre('zelle');
         $metodo_pago->setDescripcion('datos cuenta etc');
         $metodo_pago->setActivo(true);
         $gerencia->addMetodoPago($metodo_pago);

         $perfil = new Perfil();
         $perfil->setNickname('nick_demo'); 
         $perfil->setGerencia($gerencia);  
         $perfil->setActivo(true);
         $perfil->setSaldoIlimitado(true);

         $user = new User();
         $user->addPerfil($perfil);         
         $user->setEmail('gerencia1@demo.com');
         $user->setNombre('gerencia');
         $user->setApellido('demo');
         $user->setTelefono('+584141252323');
         $user->setPassword($this->passwordEncoder->encodePassword(
             $user,
             '1234567890'
         ));
         $user->setPerfil($perfil);

        $user->setRoles($roles);
        $manager->persist($user); 


//-------------USUARIO 1
        $roles = null;
        $roles[] = 'ROLE_USER';

       $perfil = new Perfil();
         $perfil->setNickname('userA'); 
         $perfil->setGerencia($gerencia);  
         $perfil->setActivo(true);
         $perfil->setSaldoIlimitado(true);

         $user = new User();
         $user->addPerfil($perfil);         
         $user->setEmail('usuario1@demo.com');
         $user->setNombre('user');
         $user->setApellido('A');
         $user->setTelefono('+584268963354');
         $user->setPassword($this->passwordEncoder->encodePassword(
             $user,
             '1234567890'
         ));
         $user->setPerfil($perfil);

        $user->setRoles($roles);
        $manager->persist($user); 


//----------USUARIO 2

       $perfil = new Perfil();
         $perfil->setNickname('userB'); 
         $perfil->setGerencia($gerencia);  
         $perfil->setActivo(true);
         $perfil->setSaldoIlimitado(false);

         $user = new User();
         $user->addPerfil($perfil);         
         $user->setEmail('usuario2@demo.com');
         $user->setNombre('usuario2');
         $user->setApellido('B');
         $user->setTelefono('+58414125552');
         $user->setPassword($this->passwordEncoder->encodePassword(
             $user,
             '1234567890'
         ));
         $user->setPerfil($perfil);

        $user->setRoles($roles);
        $manager->persist($user);      



        $manager->flush();

    }
}