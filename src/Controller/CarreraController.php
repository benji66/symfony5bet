<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Form\CarreraType;
use App\Repository\CarreraRepository;
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
 * @Route("/carrera")
 */
class CarreraController extends AbstractController
{
    /**
     * @Route("/", name="carrera_index", methods={"GET"})
     */
    public function index(CarreraRepository $carreraRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $carrera = new Carrera();
        $form = $this->createForm(CarreraType::class, $carrera);
        
        
        $allRowsQuery = $carreraRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("carrera")) {
            $val = $request->query->get("carrera");
          
            $carrera->setEmail($val['email']);
            
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
        return $this->render('carrera/index.html.twig', [
            'carreras' => $rows,
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/new", name="carrera_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser(); 
        $carrera = new Carrera();

        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             $carrera->setStatus('PENDIENTE');
             $carrera->setGerencia($user->getPerfil()->getGerencia());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($carrera);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('carrera_index');
        }

        return $this->render('carrera/new.html.twig', [
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="carrera_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Carrera $carrera): Response
    {
        return $this->render('carrera/show.html.twig', [
            'carrera' => $carrera,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="carrera_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Carrera $carrera): Response
    {
        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('carrera_index');
        }

        return $this->render('carrera/edit.html.twig', [
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/abrir", name="carrera_abrir", methods={"GET","POST"})
     */
    public function abrir(Request $request, Carrera $carrera): Response
    {
       
            $carrera->setStatus("ABIERTO");  
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('carrera_index');
       
    }    

    /**
     * @Route("/{id}/finalizar", name="carrera_finalizar", methods={"GET","POST"})
     */
    public function finalizar(Request $request, Carrera $carrera): Response
    {       
       

         
                    if($request->get("pos")){
                        
                        /*foreach ($request->get("pos") as $pos) {      
                           echo $pos.',';
                        }
                        exit;*/

                           $carrera->setOrdenOficial($request->get("pos"));
                           $carrera->setStatus('ORDEN');

                           $this->getDoctrine()->getManager()->flush();

                           $this->addFlash(
                            'success',
                            'Your changes were saved!'
                            );

                            return $this->redirectToRoute('carrera_index');
                    }     
     

        return $this->render('carrera/finalizar.html.twig', [
            'carrera' => $carrera,
           
        ]);      
    }

    /**
     * @Route("/{id}/cerrar", name="carrera_cerrar", methods={"GET","POST"})
     */
    public function cerrar(Request $request, Carrera $carrera): Response
    {       
            $carrera->setStatus("CERRADO");  
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('carrera_index');       
    }
    /**
     * @Route("/{id}", name="carrera_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Carrera $carrera): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carrera->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($carrera);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('carrera_index');
    }

     /**
     * @Route("/pdf", name="carrera_pdf", methods={"GET"})
     */
     public function getPdf(CarreraRepository $carreraRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $carreraRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        /*if ($request->query->get("carrera")) {
            $val = $request->query->get("carrera");
          
                        
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

        $html = $this->renderView('carrera/pdf.html.twig', [
            'carreras' => $allRowsQuery
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
