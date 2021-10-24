<?php

namespace App\Controller;

use App\Entity\Perfil;
use App\Form\PerfilType;
use App\Form\PerfilGerenciaType;
use App\Repository\PerfilRepository;
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
 * @Route("/perfil")
 */
class PerfilController extends AbstractController
{
    /**
     * @Route("/", name="perfil_index", methods={"GET"})
     */
    public function index(PerfilRepository $perfilRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $perfil = new Perfil();
        $form = $this->createForm(PerfilType::class, $perfil);
        
        
        $allRowsQuery = $perfilRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("perfil")) {
            $val = $request->query->get("perfil");
          
            $perfil->setEmail($val['email']);
            
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
        return $this->render('perfil/index.html.twig', [
            'perfils' => $rows,
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="perfil_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfil = new Perfil();
        $form = $this->createForm(PerfilType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($perfil);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('perfil_index');
        }

        return $this->render('perfil/new.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="perfil_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Perfil $perfil): Response
    {
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $perfil->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            $this->redirectToRoute('user_index');
        } 

        return $this->render('perfil/show.html.twig', [
            'perfil' => $perfil,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="perfil_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Perfil $perfil): Response
    {
        $form = $this->createForm(PerfilGerenciaType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('perfil/edit.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }



  
}
