<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Perfil;
use App\Form\UserType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Require ROLE_USER for *every* controller method in this class.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("/", name="profile_show", methods={"GET","POST"})
     */
    public function show(Request $request): Response
    {
       // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); 

      
        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        //return new Response('Well hi there '.$user->getPerfil()->getNombre());
        return $this->render('profile/show.html.twig', [
            'user' => $user,
           
        ]);
    }

    /**
     * @Route("/update", name="profile_update", methods={"GET","POST"})
     */
    public function update(Request $request): Response
    {

                // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); 

      
        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        //return new Response('Well hi there '.$user->getPerfil()->getNombre());
        return $this->render('profile/update.html.twig', [
            'user' => $user,
            'perfils' => $user->getPerfils(),
           
        ]);
    } 

    /**
     * @Route("/new", name="profile_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfil = new Perfil();
       
        $user = $this->getUser(); 
        $perfil->setUsuario($user);

        $form = $this->createForm(ProfileType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $perfil->setActivo(true);

            

            $entityManager->persist($perfil);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('profile_update');
        }

        return $this->render('profile/new.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }   

    /**
     * @Route("/{id}/change", name="profile_change", methods={"GET","POST"})
     */
    public function change(Request $request, Perfil $perfil): Response
    {

            $entityManager = $this->getDoctrine()->getManager();
            
            
            $perfil->getUsuario()->setGerencia($perfil->getGerencia()); 
            $perfil->getUsuario()->setSaldo($perfil->getSaldo());
            $perfil->getUsuario()->setNickname($perfil->getNickname()); 
            $perfil->getUsuario()->setRoles($perfil->getRoles());

            //$perfil->getUsuario();
            $entityManager->persist($perfil);
            $entityManager->flush();

           $this->addFlash(
            'success',
            'Perfil cambiado!'
            );

            return $this->redirectToRoute('app_logout');
       
    }


}
