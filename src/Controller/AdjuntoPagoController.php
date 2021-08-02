<?php

namespace App\Controller;

use App\Entity\AdjuntoPago;
use App\Entity\Perfil;

use App\Form\AdjuntoPagoType;
use App\Form\AdjuntoPagoEditType;
use App\Form\AdjuntoPagoUserType;
use App\Repository\AdjuntoPagoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

//JTW
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * @Route("/adjunto/pago")
 */
class AdjuntoPagoController extends AbstractController
{
    /**
     * @Route("/", name="adjunto_pago_index", methods={"GET"})
     */
    public function index(AdjuntoPagoRepository $adjuntoPagoRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $user = $this->getUser(); 
               

        $adjuntoPago = new AdjuntoPago();
        $form = $this->createForm(AdjuntoPagoType::class, $adjuntoPago);       
        $allRowsQuery = $adjuntoPagoRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ;

             if (!$this->isGranted('ROLE_COORDINADOR')) { 

                /*echo $user->getPerfil()->getId().'-----------';
                exit;*/
                   $allRowsQuery = $allRowsQuery
                    ->andWhere('a.perfil = :perfil_id')
                    ->setParameter('perfil_id', $user->getPerfil()->getId());              
             }  

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("adjunto_pago")) {
            $val = $request->query->get("adjunto_pago");
          
            $adjuntoPago->setEmail($val['email']);
            
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.email LIKE :email')
            ->setParameter('email', '%'.$val['email'].'%');
        }*/

        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery();     
        
        // Paginate the results of the query
        $rows = $paginator->paginate(
            // Doctrine Query, not results
            $allRowsQuery,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            20
        );
        
        // Render the twig view
        return $this->render('adjunto_pago/index.html.twig', [
            'adjunto_pagos' => $rows,
            'adjunto_pago' => $adjuntoPago,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="adjunto_pago_new", methods={"GET","POST"})
     */
    public function new(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {     
         $user = $this->getUser(); 
        $adjuntoPago = new AdjuntoPago();

        if ($this->isGranted('ROLE_COORDINADOR')) {
            $form = $this->createForm(AdjuntoPagoType::class, $adjuntoPago);
             $adjuntoPago->setValidado(true);  
             $adjuntoPago->setValidadoBy($user->getUsername());
             $perfil_id = null;   
        }else{
            $form = $this->createForm(AdjuntoPagoUserType::class, $adjuntoPago);            
            $adjuntoPago->setValidadoBy(null);
            $perfil_id = $user->getPerfil()->getId();  
        } 

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             $perfil = $this->getDoctrine()->getRepository(Perfil::class)->find($request->get("adjunto_pago")['perfil_id']);
             if ($this->isGranted('ROLE_COORDINADOR')) {                

                    if($adjuntoPago->getValidado()){
                      $perfil->setSaldo($perfil->getSaldo() + $adjuntoPago->getMonto());
                    }
             }  

             $ruta_relativa = '/data/uploads/'; 
              //ruta absoluta para manupilar el archivo
             $ruta = $this->getParameter('kernel.project_dir').'/public'.$ruta_relativa;                  
                     
                    if($request->get("archivo")){
                        @mkdir($ruta.'../'.$perfil->getId().'/');
                        foreach ($request->get("archivo") as $archivo) {      
                            $nueva_ruta = $ruta.'../'.$perfil->getId().'/'.$archivo;
                            $nueva_ruta_relativa = $ruta_relativa.'../'.$perfil->getId().'/'.$archivo;           
                            $adjuntoPago->setRuta($nueva_ruta_relativa);
                            rename ($ruta.$archivo, $nueva_ruta);
                        }
                    }     
       

            

            $adjuntoPago->setGerencia($perfil->getGerencia());
            $adjuntoPago->setPerfil($perfil);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($adjuntoPago);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('adjunto_pago_index');
        }


        return $this->render('adjunto_pago/new.html.twig', [
            'adjunto_pago' => $adjuntoPago,
            'form' => $form->createView(),
            'token' => $JWTManager->create($user),
            'perfil_id' => $perfil_id
        ]);
    }

    /**
     * @Route("/{id}", name="adjunto_pago_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(AdjuntoPago $adjuntoPago): Response
    {
        return $this->render('adjunto_pago/show.html.twig', [
            'adjunto_pago' => $adjuntoPago,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="adjunto_pago_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, AdjuntoPago $adjuntoPago): Response
    {
        $form = $this->createForm(AdjuntoPagoEditType::class, $adjuntoPago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $user = $this->getUser();
            
            $adjuntoPago->setValidadoBy($user->getUsername());
            
           

                if($adjuntoPago->getValidado()=='1'){
                  $adjuntoPago->getPerfil()->setSaldo($adjuntoPago->getPerfil()->getSaldo() + $adjuntoPago->getMonto());
                }

            $this->getDoctrine()->getManager()->persist($adjuntoPago);
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('adjunto_pago_index');
        }

        return $this->render('adjunto_pago/edit.html.twig', [
            'adjunto_pago' => $adjuntoPago,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="adjunto_pago_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AdjuntoPago $adjuntoPago): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adjuntoPago->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($adjuntoPago);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('adjunto_pago_index');
    }

     /**
     * @Route("/pdf", name="adjunto_pago_pdf", methods={"GET"})
     */
     public function getPdf(AdjuntoPagoRepository $adjuntoPagoRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $adjuntoPagoRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        /*if ($request->query->get("adjunto_pago")) {
            $val = $request->query->get("adjunto_pago");
          
                        
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

        $html = $this->renderView('adjunto_pago/pdf.html.twig', [
            'adjunto_pagos' => $allRowsQuery
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
