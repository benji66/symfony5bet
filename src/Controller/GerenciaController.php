<?php

namespace App\Controller;

use App\Entity\Gerencia;
use App\Form\GerenciaType;
use App\Repository\GerenciaRepository;
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
 * @Route("/gerencia")
 */
class GerenciaController extends AbstractController
{
    /**
     * @Route("/", name="gerencia_index", methods={"GET"})
     */
    public function index(GerenciaRepository $gerenciaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $gerencium = new Gerencia();
        $form = $this->createForm(GerenciaType::class, $gerencium);
        
        
        $allRowsQuery = $gerenciaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        if ($request->query->get("gerencia")) {
            $val = $request->query->get("gerencia");
          
            $gerencium->setnombre($val['nombre']);
            
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.nombre LIKE :nombre')
            ->setParameter('nombre', '%'.$val['nombre'].'%');
        }

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
        return $this->render('gerencia/index.html.twig', [
            'gerencias' => $rows,
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="gerencia_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $gerencium = new Gerencia();
        $form = $this->createForm(GerenciaType::class, $gerencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($gerencium);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('gerencia_index');
        }

        return $this->render('gerencia/new.html.twig', [
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gerencia_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Gerencia $gerencium): Response
    {
        return $this->render('gerencia/show.html.twig', [
            'gerencium' => $gerencium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gerencia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Gerencia $gerencium): Response
    {
        $form = $this->createForm(GerenciaType::class, $gerencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('gerencia_index');
        }

        return $this->render('gerencia/edit.html.twig', [
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gerencia_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Gerencia $gerencium): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gerencium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($gerencium);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('gerencia_index');
    }

     /**
     * @Route("/pdf", name="gerencia_pdf", methods={"GET"})
     */
     public function getPdf(GerenciaRepository $gerenciaRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $gerenciaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        if ($request->query->get("gerencium")) {
            $val = $request->query->get("gerencium");
          
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.nombre LIKE :nombre')
            ->setParameter('nombre', '%'.$val['nombre'].'%');
        }

        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery()->getResult();   
 

      
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('gerencia/pdf.html.twig', [
            'gerencias' => $allRowsQuery
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
