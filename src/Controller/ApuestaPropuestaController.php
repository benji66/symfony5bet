<?php

namespace App\Controller;

use App\Entity\ApuestaPropuesta;
use App\Form\ApuestaPropuestaType;
use App\Repository\ApuestaPropuestaRepository;
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
 * @Route("/apuesta/propuesta")
 */
class ApuestaPropuestaController extends AbstractController
{
    /**
     * @Route("/", name="apuesta_propuesta_index", methods={"GET"})
     */
    public function index(ApuestaPropuestaRepository $apuestaPropuestaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $apuestaPropuestum = new ApuestaPropuesta();
        $form = $this->createForm(ApuestaPropuestaType::class, $apuestaPropuestum);
        
        
        $allRowsQuery = $apuestaPropuestaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("apuesta_propuestum")) {
            $val = $request->query->get("apuesta_propuestum");
          
            $apuestaPropuestum->setEmail($val['email']);
            
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
        return $this->render('apuesta_propuesta/index.html.twig', [
            'apuesta_propuestas' => $rows,
            'apuesta_propuestum' => $apuestaPropuestum,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="apuesta_propuesta_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $apuestaPropuestum = new ApuestaPropuesta();
        $form = $this->createForm(ApuestaPropuestaType::class, $apuestaPropuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($apuestaPropuestum);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('apuesta_propuesta_index');
        }

        return $this->render('apuesta_propuesta/new.html.twig', [
            'apuesta_propuestum' => $apuestaPropuestum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="apuesta_propuesta_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(ApuestaPropuesta $apuestaPropuestum): Response
    {
        return $this->render('apuesta_propuesta/show.html.twig', [
            'apuesta_propuestum' => $apuestaPropuestum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="apuesta_propuesta_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ApuestaPropuesta $apuestaPropuestum): Response
    {
        $form = $this->createForm(ApuestaPropuestaType::class, $apuestaPropuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('apuesta_propuesta_index');
        }

        return $this->render('apuesta_propuesta/edit.html.twig', [
            'apuesta_propuestum' => $apuestaPropuestum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="apuesta_propuesta_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ApuestaPropuesta $apuestaPropuestum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apuestaPropuestum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apuestaPropuestum);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('apuesta_propuesta_index');
    }


}
