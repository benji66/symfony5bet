<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserRegisterType;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('profile_show');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request,  UserPasswordEncoderInterface $passwordEncoder): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('profile_show');
         }
      
        $user = new User();      
      
        $form = $this->createForm(UserRegisterType::class, $user);
        $form->handleRequest($request);       


        if ($form->isSubmitted() && $form->isValid()) {    

            $entityManager = $this->getDoctrine()->getManager();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);    

            $entityManager->persist($user);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('security/register.html.twig',  [
            
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

   
}
