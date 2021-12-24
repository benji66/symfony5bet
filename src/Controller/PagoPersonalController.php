<?php

namespace App\Controller;

use App\Entity\PagoPersonal;
use App\Entity\PagoPersonalSaldo;
use App\Form\PagoPersonalType;
use App\Form\PagoPersonalAsignacionType;
use App\Form\PagoPersonalSaldoType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


//pdf
use Dompdf\Dompdf;
use Dompdf\Options;



/**
 * @Route("/pago_personal")
 */
class PagoPersonalController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/", name="pago_personal_index")
     */
    public function index()
    {      

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

        $user = $this->getUser();
        /*
        echo $user->getPerfil()->getGerencia()->getId();
        echo date('Y-m-d', strtotime( date('Y-m-d').' - 6 month')).' 11:59:59';

        exit;*/

          
        $repository = $this->getDoctrine()->getRepository(PagoPersonal::class);
        $allRowsQuery = $repository->createQueryBuilder('a'); 
   
            $allRowsQuery = $allRowsQuery
           // ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')
            
            ->orderBy('a.createdAt', 'DESC')
            //->setParameter('fecha2', date('Y-m-d').' 11:59:59')
            //->setParameter('fecha1', date('Y-m-d', strtotime( date('Y-m-d').' - 6 month')).' 00:00:00')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());


        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();

        /*foreach ($allRowsQuery as $row) {
            echo $row->getCreatedAt().'--';
        }
        exit;  */

        return $this->render('pago_personal/index.html.twig', [
            'pagos' => $allRowsQuery,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/bysaldo", name="pago_personal_bysaldo")
     */
    public function bysaldo()
    {      

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

        $user = $this->getUser();
          
        $repository = $this->getDoctrine()->getRepository(PagoPersonalSaldo::class);
        $allRowsQuery = $repository->createQueryBuilder('a'); 
   
            $allRowsQuery = $allRowsQuery
           // ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')
            
            ->orderBy('a.createdAt', 'DESC')
            //->setParameter('fecha2', date('Y-m-d').' 11:59:59')
            //->setParameter('fecha1', date('Y-m-d', strtotime( date('Y-m-d').' - 6 month')).' 00:00:00')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());


        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();

        /*foreach ($allRowsQuery as $row) {
            echo $row->getCreatedAt().'--';
        }
        exit;  */

        return $this->render('pago_personal/bysaldo.html.twig', [
            'pagos' => $allRowsQuery,
        ]);
    }    


    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/new", name="pago_personal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $entidad = new PagoPersonal();
        $form = $this->createForm(PagoPersonalType::class, $entidad);
        $form->handleRequest($request);

        $perfil = $this->getUser()->getPerfil();

        if ($form->isSubmitted() && $form->isValid()) { 

            $saldo_gerencia = $entidad->getPerfil()->getGerencia()->getSaldoAcumulado();
            //$saldo_perfil = $entidad->getPerfil()->getSaldo();

            $entidad->getPerfil()->getGerencia()->setSaldoAcumulado($saldo_gerencia - $entidad->getMonto());
            
            //$entidad->getPerfil()->setSaldo($saldo_perfil + $entidad->getMonto());
            
            $entidad->setConcepto('PAGO');

            if($entidad->getMonto() < 0)
                $entidad->setConcepto('DESCUENTO');


             $ruta_relativa = '/data/uploads/'; 
            //ruta absoluta para manupilar el archivo
             $ruta = $this->getParameter('kernel.project_dir').'/public'.$ruta_relativa;                  
                     
                    if($request->get("archivo")){
                        @mkdir($ruta.'../'.$perfil->getId().'/');
                        foreach ($request->get("archivo") as $archivo) {      
                            $nueva_ruta = $ruta.'../'.$perfil->getId().'/'.$archivo;
                            $nueva_ruta_relativa = $ruta_relativa.'../'.$perfil->getId().'/'.$archivo;           
                            $entidad->setRuta($nueva_ruta_relativa);
                            rename ($ruta.$archivo, $nueva_ruta);
                        }
                   }else{
                          $this->addFlash(
                            'danger',
                            'Error en la carga del archivo'
                            );

                           return $this->redirectToRoute('pago_personal_index');
                    }      
       

            
            $entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($perfil);
            $entityManager->persist($entidad);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('pago_personal_index');
        }

        return $this->render('pago_personal/new.html.twig', [
            'pago' => $entidad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/asignacion", name="pago_personal_asignacion", methods={"GET","POST"})
     */
    public function asignacion(Request $request): Response
    {
        $entidad = new PagoPersonal();
        $form = $this->createForm(PagoPersonalAsignacionType::class, $entidad);
        $form->handleRequest($request);

        $perfil = $this->getUser()->getPerfil();

        if ($form->isSubmitted() && $form->isValid()) { 

            $saldo_gerencia = $entidad->getPerfil()->getGerencia()->getSaldoAcumulado();    
            $entidad->getPerfil()->getGerencia()->setSaldoAcumulado($saldo_gerencia - $entidad->getMonto());            
       
            
            $entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($perfil);
            $entityManager->persist($entidad);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('pago_personal_index');
        }

        return $this->render('pago_personal/asignacion.html.twig', [
            'pago' => $entidad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/{id}", name="pago_personal_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(PagoPersonal $pago): Response
    {
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $pago->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );

            return $this->redirectToRoute('pago_personal_bysaldo');
        }    

        return $this->render('pago_personal/show.html.twig', [
            'pago' => $pago,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/bysaldo/{id}", name="pago_personal_show_bysaldo", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show_bysaldo(PagoPersonalSaldo $pago): Response
    {
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $pago->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            return $this->redirectToRoute('pago_personal_bysaldo');
        } 

        return $this->render('pago_personal/show_bysaldo.html.twig', [
            'pago' => $pago,
        ]);
    }    
    /**
    * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/nomina", name="pago_personal_nomina", methods={"GET"})
     */
    public function nomina(): Response
    {
        $gerencia = $this->getUser()->getPerfil()->getGerencia();

        return $this->render('pago_personal/nomina.html.twig', [
            'perfils' => $gerencia->getPerfils(),
        ]);
    }
 
    /**
    * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/pagar_nomina", name="pago_personal_pagar_nomina", methods={"GET"})
     */
    public function pagarNomina(): Response
    {
        
        $gerencia = $this->getUser()->getPerfil()->getGerencia();
        $perfiles = $gerencia->getPerfils();
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($perfiles as $row) {
           if($row->getSueldo() > 0 && $row->getActivo()){
              //echo $row->getNickname().'-';
                $entidad = new PagoPersonalSaldo();
                $saldo_gerencia = $row->getGerencia()->getSaldoAcumulado();
                $saldo_perfil = $row->getSaldo();

                $row->getGerencia()->setSaldoAcumulado($saldo_gerencia - $row->getSueldo());

                                
                $row->setSaldo($saldo_perfil + $row->getSueldo());
                
                $entidad->setMonto($row->getSueldo());
                $entidad->setPerfil($row);
                $entidad->setConcepto('NOMINA');
                $entityManager->persist($entidad);
           }
        }        
          
        
            $entityManager->flush();
        
            $this->addFlash(
            'success',
            'Nomina pagada.'
            );
        return $this->redirectToRoute('pago_personal_bysaldo');

    }




    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/new_bysaldo", name="pago_personal_new_bysaldo", methods={"GET","POST"})
     */
    public function newBysaldo(Request $request): Response
    {
        $entidad = new PagoPersonalSaldo();
        $form = $this->createForm(PagoPersonalSaldoType::class, $entidad);
        $form->handleRequest($request);

        $perfil = $this->getUser()->getPerfil();

        if ($form->isSubmitted() && $form->isValid()) { 

            $saldo_gerencia = $entidad->getPerfil()->getGerencia()->getSaldoAcumulado();
            $saldo_perfil = $entidad->getPerfil()->getSaldo();

            $entidad->getPerfil()->getGerencia()->setSaldoAcumulado($saldo_gerencia - $entidad->getMonto());
            
            $entidad->getPerfil()->setSaldo($saldo_perfil + $entidad->getMonto());
            
            $entidad->setConcepto('PAGO');

            if($entidad->getMonto() < 0)
                $entidad->setConcepto('DESCUENTO');

                       
            $entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($perfil);
            $entityManager->persist($entidad);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('pago_personal_bysaldo');
        }

        return $this->render('pago_personal/new_bysaldo.html.twig', [
            'pago' => $entidad,
            'form' => $form->createView(),
        ]);
    }    

}
