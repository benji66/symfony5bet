<?php

namespace App\Controller;

use App\Entity\RetiroSaldo;
use App\Entity\Perfil;

use App\Form\RetiroSaldoType;
use App\Form\RetiroSaldoEditType;

use App\Repository\RetiroSaldoRepository;
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/retiro/saldo")
 */
class RetiroSaldoController extends AbstractController
{
    /**
     * @Route("/", name="retiro_saldo_index", methods={"GET"})
     */
    public function index(RetiroSaldoRepository $retiroSaldoRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $user = $this->getUser(); 
               

        $retiroSaldo = new RetiroSaldo();
        $form = $this->createForm(RetiroSaldoType::class, $retiroSaldo);       
        $allRowsQuery = $retiroSaldoRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ;

             //if (!$this->isGranted('ROLE_COORDINADOR')) { 

                /*echo $user->getPerfil()->getId().'-----------';
                exit;*/
                   $allRowsQuery = $allRowsQuery
                    ->andWhere('a.perfil = :perfil_id')
                    ->setParameter('perfil_id', $user->getPerfil()->getId());              
             //}  


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
        return $this->render('retiro_saldo/index.html.twig', [
            'retiro_saldos' => $rows,
            'retiro_saldo' => $retiroSaldo,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="retiro_saldo_new", methods={"GET","POST"})
     */
    public function new(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {     
        $user = $this->getUser(); 
        $retiroSaldo = new RetiroSaldo();

        if($user->getPerfil()->getSaldo() <= 0){
            $this->addFlash(
            'danger',
            'Posee saldo negativo de '.$user->getPerfil()->getSaldo()
            );
             return $this->redirectToRoute('retiro_saldo_index');

        }


            $form = $this->createForm(RetiroSaldoType::class, $retiroSaldo);            
            $retiroSaldo->setValidadoBy(null);
            $perfil_id = $user->getPerfil()->getId();  
    

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($user->getPerfil()->getSaldo() < $retiroSaldo->getMonto()){
                $this->addFlash(
                'danger',
                'Su saldo es inferior al monto que desea retirar'
                );
                return $this->redirectToRoute('retiro_saldo_new');
            }


            $perfil = $this->getDoctrine()->getRepository(Perfil::class)->find($perfil_id);
                          
                   
            $perfil->setSaldo($perfil->getSaldo() - $retiroSaldo->getMonto());            

            $retiroSaldo->setGerencia($perfil->getGerencia());
            $retiroSaldo->setPerfil($perfil);
            $retiroSaldo->setRuta('');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($retiroSaldo);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('retiro_saldo_index');
        }


        return $this->render('retiro_saldo/new.html.twig', [
            'retiro_saldo' => $retiroSaldo,
            'form' => $form->createView(),
            'token' => $JWTManager->create($user),
            'perfil_id' => $perfil_id
        ]);
    }

    /**
     * @Route("/{id}", name="retiro_saldo_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(RetiroSaldo $retiroSaldo): Response
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

        return $this->render('retiro_saldo/show.html.twig', [
            'retiro_saldo' => $retiroSaldo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="retiro_saldo_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMINISTRATIVO")
     */
    public function edit(Request $request, RetiroSaldo $retiroSaldo): Response
    {    


        $user = $this->getUser();

        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia =$retiroSaldo->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia || $retiroSaldo->getValidado()!==NULL){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );

            return $this->redirectToRoute('pago_personal_bysaldo');
        }    

        $form = $this->createForm(RetiroSaldoEditType::class, $retiroSaldo);
        $form->handleRequest($request);

          
             $retiroSaldo->setValidadoBy($user->getUsername());
    

        if ($form->isSubmitted() && $form->isValid()) {
            
       
            $perfil = $user->getPerfil();                     
           

                if($retiroSaldo->getValidado()=='0'){
                  $retiroSaldo->getPerfil()->setSaldo($retiroSaldo->getPerfil()->getSaldo() + $retiroSaldo->getMonto());
                }


        if($retiroSaldo->getValidado()=='1'){
             $ruta_relativa = '/data/uploads/'; 
              //ruta absoluta para manupilar el archivo
             $ruta = $this->getParameter('kernel.project_dir').'/public'.$ruta_relativa;                  
                     
                    if($request->get("archivo")){
                        @mkdir($ruta.'../'.$perfil->getId().'/');
                        foreach ($request->get("archivo") as $archivo) {      
                            $nueva_ruta = $ruta.'../'.$perfil->getId().'/'.$archivo;
                            $nueva_ruta_relativa = $ruta_relativa.'../'.$perfil->getId().'/'.$archivo;           
                            $retiroSaldo->setRuta($nueva_ruta_relativa);
                            rename ($ruta.$archivo, $nueva_ruta);
                        }
                    }else{
                          $this->addFlash(
                            'danger',
                            'Error en la carga del archivo'
                            );

                            return $this->redirectToRoute('retiro_saldo_pendiente');
                    }     
        }

            

            $this->getDoctrine()->getManager()->persist($retiroSaldo);
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('retiro_saldo_pendiente');
        }

        return $this->render('retiro_saldo/edit.html.twig', [
            'retiro_saldo' => $retiroSaldo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/pendiente", name="retiro_saldo_pendiente", methods={"GET"})
     */
    public function pendiente(RetiroSaldoRepository $retiroSaldoRepository, PaginatorInterface $paginator, Request $request): Response
    {               
    

        $retiroSaldo = new RetiroSaldo();
        $form = $this->createForm(RetiroSaldoType::class, $retiroSaldo);       
        $allRowsQuery = $retiroSaldoRepository->createQueryBuilder('a')
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
            50
        );
        
        // Render the twig view
        return $this->render('retiro_saldo/pendiente.html.twig', [
            'retiro_saldos' => $rows,
            'retiro_saldo' => $retiroSaldo,
            'form' => $form->createView(),
        ]);


    }
 
}
