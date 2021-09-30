<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Cuenta;

use App\Entity\ApuestaDetalle;
use App\Entity\Apuesta;
use App\Entity\ApuestaPropuesta;
use App\Form\ApuestaPropuestaType;
use App\Form\CarreraType;
use App\Repository\CarreraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/carrera/abierta")
 * @IsGranted("ROLE_USER")
 */
class CarreraAbiertaController extends AbstractController
{
    /**
     * @Route("/", name="carrera_abierta_index", methods={"GET"})
     */
    public function index(CarreraRepository $carreraRepository, Request $request): Response
    {        
        $user = $this->getUser();

        $parametros['gerencia'] = $user->getPerfil()->getGerencia()->getId();
        $parametros['status'] = 'ABIERTO';
        $carreras = $carreraRepository->findByStatus($parametros);

        // Render the twig view
        return $this->render('apuesta_abierta/index.html.twig', [
            'carreras' => $carreras,    
           
        ]);

    }

    /**
     * @Route("/show/{id}", name="carrera_abierta_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Carrera $carrera): Response
    {
       
        return $this->render('apuesta_abierta/show.html.twig', [
            'carrera' => $carrera,
        ]);
    } 

    /**
     * @Route("/cargar/{id}", name="carrera_abierta_cargar", methods={"POST"}, requirements={"id":"\d+"})
     */
    public function cargar(Request $request, ApuestaPropuesta $propuesta): Response
    {       
        
       if($propuesta->getCarrera()->getStatus()!='ABIERTO'){
          $this->addFlash(
                   'danger',
                   'La carrera se encuentra cerrada'
              );
           return $this->redirectToRoute('carrera_abierta_index');
        }


        $perfil = $this->getUser()->getPerfil();

        $monto = $request->request->get('apuesta_monto');

        if($monto <= $propuesta->getMonto() && $monto > 0){

            if($perfil->getSaldo() >= $monto || ($perfil->getSaldoIlimitado())){ 

                $apuesta = new Apuesta();                
                $apuesta->setMonto($monto);
                $apuesta->setTipo($propuesta->getTipo());
                //$apuesta->setCarrera($propuesta->getCarrera());
                //jugador 1 - quien propone
                $apuesta_detalle = new ApuestaDetalle();
                $apuesta_detalle->setPerfil($propuesta->getJugador());
                $apuesta_detalle->setCaballos($propuesta->getCaballos());
                $apuesta->addApuestaDetalle($apuesta_detalle);

                //-quien apuesta, caballos en null ya que se rigen por los caballso propuestos
                $perfil->setSaldo($perfil->getSaldo() -  $monto);
                $apuesta_detalle = new ApuestaDetalle();
                $apuesta_detalle->setPerfil($perfil);
                $apuesta_detalle->setCaballos(null);
                $apuesta->addApuestaDetalle($apuesta_detalle);
                
                $propuesta->setMonto($propuesta->getMonto() - $monto);
                $propuesta->getCarrera()->addApuesta($apuesta);
                 
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($propuesta);        
                $entityManager->flush();

                $this->addFlash(
                  'success',
                  'Jugado!'
                ); 

            }else{
                 $this->addFlash(
                    'danger',
                    'Saldo insuficiente'
                 );
            }             

        }else{
              $this->addFlash(
                   'danger',
                   'El monto jugado es invalido'
              );
        }

        return $this->redirectToRoute('carrera_abierta_show', ['id'=>$propuesta->getCarrera()->getId()]);
        
    }     

    /**
     * @Route("/new/{id}", name="carrera_abierta_new", methods={"GET","POST"})
     */
    public function new(Request $request, Carrera $carrera): Response
    {       
      
        $perfil = $this->getUser()->getPerfil();
        $apuestaPropuestum = new ApuestaPropuesta();
        $form = $this->createForm(ApuestaPropuestaType::class, $apuestaPropuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $caballos = $request->get("datos_caballos");
            if($caballos){

                 if(($perfil->getSaldo() >= $apuestaPropuestum->getMonto()) || ($perfil->getSaldoIlimitado())){
                      
                      $perfil->setSaldo($perfil->getSaldo() -  $apuestaPropuestum->getMonto() ); 
                   
                      $apuestaPropuestum->setJugador($perfil);

                        $apuestaPropuestum->setCaballos($caballos);
                        $apuestaPropuestum->setCarrera($carrera);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($apuestaPropuestum);
                        $entityManager->flush();

                         $this->addFlash(
                                'success',
                                'Propuesta realizada!'
                            ); 
                        return $this->redirectToRoute('carrera_abierta_show', ['id'=>$carrera->getId()]);

                 }else{                    
                     $this->addFlash(
                        'danger',
                        'Error: saldo insuficiente ('.$perfil->getSaldo().')'
                     );                     
                 }          


            }else{
                 $this->addFlash(
                        'danger',
                        'Error: No se agregaron caballos'
                    );
            }

        }

        return $this->render('apuesta_abierta/new.html.twig', [
            'apuesta_propuestum' => $apuestaPropuestum,
            'form' => $form->createView(),
            'carrera' => $carrera,
        ]);
    }  

}
