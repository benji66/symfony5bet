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
 * @IsGranted("ROLE_COORDINADOR")
 */
class MetodoPagoController extends AbstractController
{
    /**
     * @Route("/", name="metodo_pago_index", methods={"GET"})
     */
    public function index(MetodoPagoRepository $metodoPagoRepository, PaginatorInterface $paginator, Request $request): Response
    {
        

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

           $metodoPago->setGerencia($user->getGerencia());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($metodoPago);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
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
        return $this->render('metodo_pago/show.html.twig', [
            'metodo_pago' => $metodoPago,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="metodo_pago_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MetodoPago $metodoPago): Response
    {
        $form = $this->createForm(MetodoPagoType::class, $metodoPago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
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
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('metodo_pago_index');
    }

     /**
     * @Route("/pdf", name="metodo_pago_pdf", methods={"GET"})
     */
     public function getPdf(MetodoPagoRepository $metodoPagoRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $metodoPagoRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        /*if ($request->query->get("metodo_pago")) {
            $val = $request->query->get("metodo_pago");
          
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.email LIKE :email')
            ->setParameter('email', '%'.$val['email'].'%');
        }*/

        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery()->getResult();   
 

      
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('metodo_pago/pdf.html.twig', [
            'metodo_pagos' => $allRowsQuery
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);        
    }    
}
