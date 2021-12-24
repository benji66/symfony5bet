<?php

namespace App\Controller;

use App\Entity\PagoCliente;
use App\Entity\PagoClienteSaldo;
use App\Form\PagoClienteType;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


//pdf
use Dompdf\Dompdf;
use Dompdf\Options;



/**
 * @Route("/pago_cliente")
 */
class PagoClienteController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/", name="pago_cliente_index")
     */
    public function index()
    {      

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

        $user = $this->getUser();
        /*
        echo $user->getPerfil()->getGerencia()->getId();
        echo date('Y-m-d', strtotime( date('Y-m-d').' - 6 month')).' 11:59:59';

        exit;*/

          
        $repository = $this->getDoctrine()->getRepository(PagoCliente::class);
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

        return $this->render('pago_cliente/index.html.twig', [
            'pagos' => $allRowsQuery,
        ]);
    }



    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/new", name="pago_cliente_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $entidad = new PagoCliente();
        $form = $this->createForm(PagoClienteType::class, $entidad);
        $form->handleRequest($request);

        $perfil = $this->getUser()->getPerfil();

        if ($form->isSubmitted() && $form->isValid()) { 


            if($entidad->getMonto()<=0){
                $this->addFlash(
                        'danger',
                        'Monto invalido'
                        );                  

                return $this->redirectToRoute('pago_cliente_index');
            }


            $saldo = $entidad->getPerfil()->getSaldo();

            if($entidad->getMonto()  > $saldo){
                $this->addFlash(
                        'danger',
                        $entidad->getPerfil()->getNickname().' no posee saldo suficiente ('.$entidad->getPerfil()->getSaldo().')'
                        );                  

                return $this->redirectToRoute('pago_cliente_index');
            }
            //$saldo_perfil = $entidad->getPerfil()->getSaldo();

           $entidad->getPerfil()->setSaldo($saldo - $entidad->getMonto());
            
       
            
            $entidad->setConcepto('PAGO');

            


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

                           return $this->redirectToRoute('pago_cliente_index');
                    }        
       

            
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($entidad);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('pago_cliente_index');
        }

        return $this->render('pago_cliente/new.html.twig', [
            'pago' => $entidad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMINISTRATIVO")
     * @Route("/{id}", name="pago_cliente_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(PagoCliente $pago): Response
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


}
