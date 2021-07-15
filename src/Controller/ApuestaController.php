<?php

namespace App\Controller;

use App\Entity\Apuesta;
use App\Entity\ApuestaDetalle;
use App\Entity\Carrera;
use App\Entity\Perfil;
use App\Form\ApuestaType;
use App\Repository\ApuestaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/apuesta")
 */
class ApuestaController extends AbstractController
{
    /**
     * @Route("/", name="apuesta_index", methods={"GET"})
     */
    public function index(ApuestaRepository $apuestaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $apuestum = new Apuesta();
        $form = $this->createForm(ApuestaType::class, $apuestum);
        
        
        $allRowsQuery = $apuestaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("apuestum")) {
            $val = $request->query->get("apuestum");
          
            $apuestum->setEmail($val['email']);
            
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
            2
        );
        
        // Render the twig view
        return $this->render('apuesta/index.html.twig', [
            'apuestas' => $rows,
            'apuestum' => $apuestum,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/{id}/new", name="apuesta_new", methods={"GET","POST"})
     */
    public function new(Request $request, Carrera $carrera): Response
    {
        $apuestum = new Apuesta();
        $form = $this->createForm(ApuestaType::class, $apuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {



            $apuestum->setCarrera($carrera);
            
            $perfiles = $request->get("datos_perfil");
           
            /*echo '<pre>';
            print_r($perfiles);
            echo '</pre>';
            exit;*/
            
            $faltaSaldo=false;
            foreach ($perfiles as $row ) {                
                 $perfil = $this->getDoctrine()->getRepository(Perfil::class)->find($row['perfil_id']);

                 if(($perfil->getSaldo() >= $apuestum->getMonto()) || ($perfil->getSaldoIlimitado())){
                      $perfil->setSaldo($perfil->getSaldo() -  $apuestum->getMonto() );
                      
                      $apuesta_detalle = new ApuestaDetalle();
                      $apuesta_detalle->setPerfil($perfil);

                      $apuesta_detalle->setCaballos($row['caballos']);

                      $apuestum->addApuestaDetalle($apuesta_detalle);   

                 }else{                    
                     $this->addFlash(
                        'error',
                        $perfil->getNickname().' no posee saldo suficiente ('.$perfil->getSaldo().')'
                        );
                      $faltaSaldo=true;
                 }                 
            }

            if(!$faltaSaldo){     

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($apuestum);        
                    $entityManager->flush();        

                     $this->addFlash(
                    'success',
                    'Your changes were saved-!'
                    );
              }

          //resetea el form
            $apuestum = new Apuesta();
            $form = $this->createForm(ApuestaType::class, $apuestum);

            //return $this->redirectToRoute('apuesta_index');
        }

        return $this->render('apuesta/new.html.twig', [
            'apuestum' => $apuestum,
            'form' => $form->createView(),
            'apuestas' => $carrera->getApuestas(),
            'carrera' => $carrera,
        ]);
    }

    /**
     * @Route("/{id}", name="apuesta_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Apuesta $apuestum): Response
    {
        return $this->render('apuesta/show.html.twig', [
            'apuestum' => $apuestum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="apuesta_edit", methods={"GET","POST"})
     */
    /*public function edit(Request $request, Apuesta $apuestum): Response
    {
        $form = $this->createForm(ApuestaType::class, $apuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('apuesta_index');
        }

        return $this->render('apuesta/edit.html.twig', [
            'apuestum' => $apuestum,
            'form' => $form->createView(),
        ]);
    }*/

    /**
     * @Route("/{id}", name="apuesta_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Apuesta $apuestum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apuestum->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $rows = $apuestum->getApuestaDetalles(); 

            foreach ($rows as $row) {                
                      $row->getPerfil()->setSaldo($row->getPerfil()->getSaldo() + $apuestum->getMonto());
                      $entityManager->persist($row);                         
            }           
            
            $entityManager->remove($apuestum);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('apuesta_new', ['id'=>$apuestum->getCarrera()->getId()]);
    }

     /**
     * @Route("/pdf", name="apuesta_pdf", methods={"GET"})
     */
     public function getPdf(ApuestaRepository $apuestaRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $apuestaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        /*if ($request->query->get("apuestum")) {
            $val = $request->query->get("apuestum");
          
                        
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

        $html = $this->renderView('apuesta/pdf.html.twig', [
            'apuestas' => $allRowsQuery
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
