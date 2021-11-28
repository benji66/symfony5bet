<?php

namespace App\Controller;

use App\Entity\PerfilBanco;
use App\Form\PerfilBancoType;
use App\Repository\PerfilBancoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/perfil/banco")
 * @IsGranted("ROLE_USER")
 */
class PerfilBancoController extends AbstractController
{
    /**
     * @Route("/", name="perfil_banco_index", methods={"GET"})
     */
    public function index(PerfilBancoRepository $perfilBancoRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
//
        $user = $this->getUser();

        //$rows = $user->getPerfil()->getPerfilBancos();  

        $rows = $perfilBancoRepository->findByPerfil($user->getPerfil()->getId());

        return $this->render('perfil_banco/index.html.twig', [
            'perfil_bancos' => $rows,
            //'perfil_banco' => $perfilBanco,            
        ]);


    }

    /**
     * @Route("/new", name="perfil_banco_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfilBanco = new PerfilBanco();
        $form = $this->createForm(PerfilBancoType::class, $perfilBanco);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user =  $user = $this->getUser(); 

           $perfilBanco->setPerfil($user->getPerfil());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($perfilBanco);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('perfil_banco_index');
        }

        return $this->render('perfil_banco/new.html.twig', [
            'perfil_banco' => $perfilBanco,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="perfil_banco_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(PerfilBanco $perfilBanco): Response
    {

        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia =  $perfilBanco->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );
            return $this->redirectToRoute('adjunto_pago_index');
        }   

        return $this->render('perfil_banco/show.html.twig', [
            'perfil_banco' => $perfilBanco,
        ]);
    }


    /**
     * @Route("/{id}", name="perfil_banco_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PerfilBanco $perfilBanco): Response
    {
        if ($this->isCsrfTokenValid('delete'.$perfilBanco->getId(), $request->request->get('_token'))) {
            
            $entityManager = $this->getDoctrine()->getManager();

            if(!count($perfilBanco->getRetiroSaldos())){                           
               
                $entityManager->remove($perfilBanco);     
               
            }else{
                $perfilBanco->setActivo(false);
                $entityManager->persist($perfilBanco);
                
            }

            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('perfil_banco_index');
    }

}
