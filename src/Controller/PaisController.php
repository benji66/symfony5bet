<?php

namespace App\Controller;

use App\Entity\Pais;
use App\Form\PaisType;
use App\Repository\PaisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/pais")
 */
class PaisController extends AbstractController
{
   
   /**
    * @Route("/servicio/{valor}", name="pais_servicio", methods={"GET"})
    */
    public function servicio(Request $request, ValidatorInterface $validator)
    {      

        $valor = $request->get("valor");
        $pais = new Pais();
        $pais->setNombre($valor);
        $errors = $validator->validate($pais);        
             
        if (count($errors) > 0) {
           
            foreach($errors as $error){
                $errores[] = $error->getMessage();    
            }
            
            $response = new JsonResponse(); 
            $response ->setData(['errores'=>$errores])
            ->setStatusCode(204);
            return $response;
        }

        $rows = $this->getDoctrine()->getRepository(Pais::class)
        ->buscarPorNombre($valor);

         if(!$rows){ 
            $response = new JsonResponse(); 
            $response->setData(['mensaje'=>'No se encontraron datos'])
            ->setStatusCode(201);
            return $response;
         }    

        $response = new JsonResponse();
        $response->setData($rows);
        return $response;     

     }          
    


   
   /**
    * @Route("/consulta", name="pais_consulta", methods={"GET"})
    */
    public function consulta(Request $request)
    {      
        
        $pai = new Pais();
        $form = $this->createForm(PaisType::class, $pai);
        // Render the twig view
        return $this->render('pais/consulta.html.twig', [
            'pai' => $pai,
            'form' => $form->createView(),
        ]);    

     }  

    /**
     * @Route("/", name="pais_index", methods={"GET"})
     */
    public function index(PaisRepository $paisRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $pai = new Pais();
        $form = $this->createForm(PaisType::class, $pai);
        
        
        $allRowsQuery = $paisRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ;     

        if ($request->query->get("pais")) {
            $val = $request->query->get("pais");
          
            $pai->setNombre($val['nombre']);
            
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
            10
        );
        
        // Render the twig view
        return $this->render('pais/index.html.twig', [
            'pais' => $rows,
            'pai' => $pai,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="pais_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pai = new Pais();
        $form = $this->createForm(PaisType::class, $pai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pai);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('pais_index');
        }

        return $this->render('pais/new.html.twig', [
            'pai' => $pai,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pais_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Pais $pai): Response
    {
        return $this->render('pais/show.html.twig', [
            'pai' => $pai,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pais_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Pais $pai): Response
    {
        $form = $this->createForm(PaisType::class, $pai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('pais_index');
        }

        return $this->render('pais/edit.html.twig', [
            'pai' => $pai,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pais_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Pais $pai): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pai->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pai);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('pais_index');
    }

     /**
     * @Route("/pdf", name="pais_pdf", methods={"GET"})
     */
     public function getPdf(PaisRepository $paisRepository, Request $request)
     {        // Configure Dompdf according to your needs

          $allRowsQuery = $paisRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

       

        if ($request->query->get("pais")) {
            $val = $request->query->get("pais");
          
                        
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

        $html = $this->renderView('pais/pdf.html.twig', [
            'pais' => $allRowsQuery
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
