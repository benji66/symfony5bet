<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gerencia;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_COORDINADOR")
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator,  Request $request): Response
    {           

      $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA');          
        
        /*$logged_user = $this->getUser();
        echo  $logged_user->getGerenciaPermiso()->getId().'--------//-----';
        exit;*/

        $user = $this->getUser();

        // Render the twig view
        return $this->render('user/index.html.twig', [
            'perfils' => $user->getPerfil()->getGerencia()->getPerfils(),          
           
        ]);


    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,  UserPasswordEncoderInterface $passwordEncoder): Response
    {        

        $user = new User();
        $user_logueado = $this->getUser();
        //echo $user_logueado->getPerfil()->getGerencia();
        //exit;        

         /*$repository = $this->getDoctrine()->getRepository(Gerencia::class);
         $gerencia = $repository->find($user_logueado->getPerfil()->getGerencia()->getId());*/       
       
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);       


        if ($form->isSubmitted() && $form->isValid()) { 

          //echo $user->getPerfil()->getNickname();
          //exit;   
             $user->getPerfil()->setGerencia($user_logueado->getPerfil()->getGerencia());
             $user->getPerfil()->setUsuario($user);

          /*$repository = $this->getDoctrine()->getRepository(User::class);
          $usuario = $repository->findOneByEmail($user->getEmail());

          if($usuario){

            $usuario
          }*/       

            $entityManager = $this->getDoctrine()->getManager();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);    

            $entityManager->persist($user);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(User $user): Response
    {

        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $user->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            $this->redirectToRoute('user_index');
        } 
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
      
        $userOld['password'] = $user->getPassword();   

        $form = $this->createForm(UserType::class, $user);
    
         $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
  
          
          //set user password old

            if(!$user->getPassword()){
                $user->setPassword($userOld['password']);
            }else{
               $password = $passwordEncoder->encodePassword($user, $user->getPassword());
               $user->setPassword($password);
            }

               
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,                                 
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        /*if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }*/

        return $this->redirectToRoute('user_index');
    }

}
