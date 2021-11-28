<?php

namespace App\Controller;

use App\Entity\MetodoPago;
use App\Form\MetodoPagoType;
use App\Repository\MetodoPagoRepository;
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
 * @Route("/metodo/pago")
 * @IsGranted("ROLE_ADMINISTRATIVO")
 */
class MetodoPagoController extends AbstractController
{
    /**
     * @Route("/", name="metodo_pago_index", methods={"GET"})
     */
    public function index(MetodoPagoRepository $metodoPagoRepository, PaginatorInterface $paginator, Request $request): Response
    {        
//
        $user = $this->getUser();

        $rows = $user->getPerfil()->getGerencia()->getMetodoPagos();       
        // Render the twig view
        return $this->render('metodo_pago/index.html.twig', [
            'metodo_pagos' => $rows,
            //'metodo_pago' => $metodoPago,            
        ]);
    }

    /**
     * @Route("/new", name="metodo_pago_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $metodoPago = new MetodoPago();
        $form = $this->createForm(MetodoPagoType::class, $metodoPago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user =  $user = $this->getUser(); 

           $metodoPago->setGerencia($user->getPerfil()->getGerencia());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($metodoPago);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('metodo_pago_index');
        }

        return $this->render('metodo_pago/new.html.twig', [
            'metodo_pago' => $metodoPago,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="metodo_pago_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(MetodoPago $metodoPago): Response
    {
       
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $metodoPago->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );
            return $this->redirectToRoute('metodo_pago_index');  
            }    
   
        return $this->render('metodo_pago/show.html.twig', [
            'metodo_pago' => $metodoPago,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="metodo_pago_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MetodoPago $metodoPago): Response
    {
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $metodoPago->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );
            return $this->redirectToRoute('metodo_pago_index');  
            }    

        $form = $this->createForm(MetodoPagoType::class, $metodoPago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('metodo_pago_index');
        }

        return $this->render('metodo_pago/edit.html.twig', [
            'metodo_pago' => $metodoPago,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="metodo_pago_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MetodoPago $metodoPago): Response
    {
        if ($this->isCsrfTokenValid('delete'.$metodoPago->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($metodoPago);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('metodo_pago_index');
    }


}
