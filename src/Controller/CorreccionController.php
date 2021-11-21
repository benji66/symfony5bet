<?php

namespace App\Controller;

use App\Entity\Correccion;
use App\Entity\Apuesta;
use App\Entity\Perfil;
use App\Form\CorreccionType;
use App\Repository\CorreccionRepository;
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
 * @Route("/correccion")
 */
class CorreccionController extends AbstractController
{
    /**
     * @Route("/", name="correccion_index", methods={"GET"})
     */
    public function index(CorreccionRepository $correccionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $correccion = new Correccion();
        $form = $this->createForm(CorreccionType::class, $correccion);
        
        
        $allRowsQuery = $correccionRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("correccion")) {
            $val = $request->query->get("correccion");
          
            $correccion->setEmail($val['email']);
            
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
        return $this->render('correccion/index.html.twig', [
            'correccions' => $rows,
            'correccion' => $correccion,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/{id}/new", name="correccion_new", methods={"GET","POST"})
     */
    public function new(Request $request, Apuesta $apuesta): Response
    {
        $correccion = new Correccion();
        $form = $this->createForm(CorreccionType::class, $correccion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             $entityManager = $this->getDoctrine()->getManager();

             $clientes = $request->get('clientes');
             $observacion_sistema =null;
             
             foreach ($clientes as $key => $row) {
                 $perfil = $this->getDoctrine()->getRepository(Perfil::class)->find($key);

                 $perfil->setSaldo($perfil->getSaldo() + $row['monto']);

                 $entityManager->persist($perfil);

                 $observacion_sistema .= $perfil->getNickname().' se le hace una correccion de '.$row['monto'].'. ';
             }
             
             $casa_monto = $request->get('casa_monto');
             $saldo_casa_acumulado = $apuesta->getCarrera()->getGerencia()->getSaldoAcumulado();             
             $apuesta->getCarrera()->getGerencia()->setSaldoAcumulado($saldo_casa_acumulado + $casa_monto);  

             $observacion_sistema .= ' A saldo de casa se le hace una correccion de '.$casa_monto.'.';
             //echo $observacion_sistema;
             $correccion->setObservacionSistema($observacion_sistema);
             //exit;

            //$entityManager->persist($apuesta);
            $correccion->setApuesta($apuesta);
            $entityManager->persist($correccion);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Correcciones realizadas!'
            );

            return $this->redirectToRoute('carrera_show', ['id' => $apuesta->getCarrera()->getId()]);
        }

        return $this->render('correccion/new.html.twig', [
            'correccion' => $correccion,
            'form' => $form->createView(),
            'apuesta' => $apuesta
        ]);
    }

    /**
     * @Route("/{id}", name="correccion_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Correccion $correccion): Response
    {
        return $this->render('correccion/show.html.twig', [
            'correccion' => $correccion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="correccion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Correccion $correccion): Response
    {
        $form = $this->createForm(CorreccionType::class, $correccion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('correccion_index');
        }

        return $this->render('correccion/edit.html.twig', [
            'correccion' => $correccion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="correccion_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Correccion $correccion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$correccion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($correccion);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('correccion_index');
    }


}
