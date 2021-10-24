<?php

namespace App\Controller;

use App\Entity\Local;
use App\Form\LocalType;
use App\Repository\LocalRepository;
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
 * @Route("/local")
 * @IsGranted("ROLE_ADMIN")
 */
class LocalController extends AbstractController
{
    /**
     * @Route("/", name="local_index", methods={"GET"})
     */
    public function index(LocalRepository $localRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $local = new Local();
        $form = $this->createForm(LocalType::class, $local);
        
        
        $allRowsQuery = $localRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("local")) {
            $val = $request->query->get("local");
          
            $local->setEmail($val['email']);
            
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
            50
        );
        
        // Render the twig view
        return $this->render('local/index.html.twig', [
            'locals' => $rows,
            'local' => $local,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="local_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $local = new Local();
        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($local);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('local_index');
        }

        return $this->render('local/new.html.twig', [
            'local' => $local,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="local_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Local $local): Response
    {
        return $this->render('local/show.html.twig', [
            'local' => $local,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="local_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Local $local): Response
    {
        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('local_index');
        }

        return $this->render('local/edit.html.twig', [
            'local' => $local,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="local_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Local $local): Response
    {
        if ($this->isCsrfTokenValid('delete'.$local->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($local);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('local_index');
    }

     /**
     * @Route("/pdf", name="local_pdf", methods={"GET"})
     */
     public function getPdf(LocalRepository $localRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
           $allRowsQuery = $localRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify

        /*if ($request->query->get("local")) {
            $val = $request->query->get("local");
          
                        
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

        $html = $this->renderView('local/pdf.html.twig', [
            'locals' => $allRowsQuery
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
