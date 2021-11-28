<?php

namespace App\Controller;

use App\Entity\Traspaso;
use App\Entity\Perfil;

use App\Form\TraspasoType;

use App\Repository\TraspasoRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;



use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/traspaso")
 */
class TraspasoController extends AbstractController
{

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/new", name="traspaso_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {     
        $user = $this->getUser(); 
        $traspaso = new Traspaso();

        $form = $this->createForm(TraspasoType::class, $traspaso);  

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if(($traspaso->getDescuento()->getSaldo() < $traspaso->getMonto()) && !$traspaso->getDescuento()->getSaldoIlimitado() ) {
                $this->addFlash(
                'danger',
                'Su saldo es inferior al monto que desea retirar'
                );
                return $this->redirectToRoute('retiro_saldo_new');
            }          
                          
                   
            $traspaso->getDescuento()->setSaldo($traspaso->getDescuento()->getSaldo() - $traspaso->getMonto());            
            $traspaso->getAbono()->setSaldo($traspaso->getAbono()->getSaldo() + $traspaso->getMonto());
            $traspaso->setGerencia($user->getPerfil()->getGerencia());        
            

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($traspaso);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('traspaso_index');
        }


        return $this->render('traspaso/new.html.twig', [
            
            'form' => $form->createView(),
        
        ]);
    }

    /**
     * @Route("/{id}", name="traspaso_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Traspaso $traspaso): Response
    {
        
        $user = $this->getUser();

        $gerencia_logueada = $user->getPerfil()->getGerencia()->getId();
        $gerencia =$retiroSaldo->getPerfil()->getGerencia()->getId();

        $id_user_logueado = $user->getPerfil()->getId();
        $id_user_row = $retiroSaldo->getPerfil()->getId();

  

        if($gerencia_logueada != $gerencia || ( !$this->isGranted('ROLE_ADMINISTRATIVO') && $id_user_logueado!=$id_user_row )){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );

            return $this->redirectToRoute('pago_personal_bysaldo');
        }  

        return $this->render('traspaso/show.html.twig', [
            'traspaso' => $traspaso,
        ]);
    }




    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/", name="traspaso_index", methods={"GET"})
     */
    public function index(TraspasoRepository $traspasoRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $allRowsQuery = $traspasoRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->where('a.gerencia = :gerencia_id')
            ->setParameter('gerencia_id',  $this->getUser()->getPerfil()->getGerencia()->getId())
            ;

        
        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery();     
        
        // Paginate the results of the query
        $rows = $paginator->paginate(
            // Doctrine Query, not results
            $allRowsQuery,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            100
        );
        
        // Render the twig view
        return $this->render('traspaso/index.html.twig', [
            'traspasos' => $rows,            
            
        ]);


    }
 
}
