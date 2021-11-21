<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Cuenta;
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
 * @IsGranted("ROLE_COORDINADOR")
 * @Route("/carrera")
 */
class CarreraController extends AbstractController
{
    /**
     * @Route("/", name="carrera_index", methods={"GET"})
     */
    public function index(CarreraRepository $carreraRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $carrera = new Carrera();
        $form = $this->createForm(CarreraType::class, $carrera);
        
        $user = $this->getUser();
        
        $allRowsQuery = $carreraRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            ->andWhere('a.gerencia = :gerencia')

            ->orderBy('a.fecha', 'DESC')
           
            //->setParameter('status', 'canceled')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());

        //example filter code, you must uncomment and modify    

        /*if ($request->query->get("carrera")) {
            $val = $request->query->get("carrera");
          
            $carrera->setEmail($val['email']);
            
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
            20
        );
        
        // Render the twig view
        return $this->render('carrera/index.html.twig', [
            'carreras' => $rows,
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/new", name="carrera_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser(); 
        $carrera = new Carrera();

        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             $carrera->setStatus('PENDIENTE');
             $carrera->setGerencia($user->getPerfil()->getGerencia());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($carrera);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('carrera_index');
        }

        return $this->render('carrera/new.html.twig', [
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="carrera_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Carrera $carrera): Response
    {
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $carrera->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );
            return $this->redirectToRoute('carrera_index');
        }    

        return $this->render('carrera/show.html.twig', [
            'carrera' => $carrera,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="carrera_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Carrera $carrera): Response
    {
        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('carrera_index');
        }

        return $this->render('carrera/edit.html.twig', [
            'carrera' => $carrera,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/abrir", name="carrera_abrir", methods={"GET","POST"})
     */
    public function abrir(Request $request, Carrera $carrera): Response
    {
       
            $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
            $gerencia = $carrera->getGerencia()->getId();

            if($gerencia_logueada != $gerencia){
                $this->addFlash(
                'danger',
                'Acceso no autorizado'
                );
                return $this->redirectToRoute('carrera_index');  
            }    

           if($carrera->getStatus()!="PENDIENTE"){

                $this->addFlash(
                'danger',
                'Operacion no permitida, status '.$carrera->getStatus()
                );
                return $this->redirectToRoute('carrera_index');  
            }
             
                $carrera->setStatus("ABIERTO");  
                $this->getDoctrine()->getManager()->flush();

               $this->addFlash(
                'success',
                'Los cambios fueron realizados!'
                );

          

            return $this->redirectToRoute('carrera_index');
       
    }    

    /**
     * @Route("/{id}/finalizar", name="carrera_finalizar", methods={"GET","POST"})
     */
    public function finalizar(Request $request, Carrera $carrera): Response
    {               
            $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
            $gerencia = $carrera->getGerencia()->getId();

            if($gerencia_logueada != $gerencia){
                $this->addFlash(
                'danger',
                'Acceso no autorizado'
                );
                return $this->redirectToRoute('carrera_index');  
            }    

             if($carrera->getStatus()!="CERRADO" && $carrera->getStatus()!="ORDEN"){
                $this->addFlash(
                'danger',
                'Operacion no permitida, status '.$carrera->getStatus()
                );
                return $this->redirectToRoute('carrera_index');  
              }
                    if($request->get("pos")){
                        
                           $carrera->setOrdenOficial($request->get("pos"));
                           $carrera->setStatus('ORDEN');

                           $this->getDoctrine()->getManager()->flush();

                           $this->addFlash(
                            'success',
                            'Los cambios fueron realizados!'
                            );

                            return $this->redirectToRoute('carrera_index');
                    }    




    

            return $this->render('carrera/finalizar.html.twig', [
            'carrera' => $carrera,
           
        ]);      
    }

    /**
     * @Route("/{id}/cerrar", name="carrera_cerrar", methods={"GET","POST"})
     */
    public function cerrar(Request $request, Carrera $carrera): Response
    {       
            
            $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
            $gerencia = $carrera->getGerencia()->getId();

            if($gerencia_logueada != $gerencia){
                $this->addFlash(
                'danger',
                'Acceso no autorizado'
                );
                return $this->redirectToRoute('carrera_index');  
            }    

           if($carrera->getStatus()!="ABIERTO"){
                $this->addFlash(
                'danger',
                'Operacion no permitida, status '.$carrera->getStatus()
                );
                return $this->redirectToRoute('carrera_index');  
           }
                $user = $this->getUser(); 
                $carrera->setStatus("CERRADO");  
                
                $carrera->setCerradoBy($user->getUsername());

                $propuestas = $carrera->getApuestaPropuestas(); 

                foreach($propuestas as $propuesta){
                   
                   $monto = $propuesta->getMonto();
                   $jugador = $propuesta->getJugador();
                   $jugador->setSaldo($jugador->getSaldo() + $monto);
                   $propuesta->setJugador($jugador);
                   $propuesta->setMonto(0);

                   $this->getDoctrine()->getManager()->persist($propuesta);

                }

      




           $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('carrera_index');       
    }

    /**
     * @Route("/{id}/pagar", name="carrera_pagar", methods={"GET","POST"})
     */
    public function pagar(Request $request, Carrera $carrera): Response
    {       

            $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
            $gerencia = $carrera->getGerencia()->getId();

            if($gerencia_logueada != $gerencia){
                $this->addFlash(
                'danger',
                'Acceso no autorizado'
                );
            }    

             if($carrera->getStatus()!='ORDEN'){
                $this->addFlash(
                'danger',
                'Operacion no permitida, status '.$carrera->getStatus()
                );

              }

    
            $entityManager = $this->getDoctrine()->getManager();
            $user = $this->getUser(); 
            $carrera->setStatus("PAGADO");
            $carrera->setPagadoBy($user->getUsername());
            $apuestas = $carrera->getApuestas();
            $orden = $carrera->getOrdenOficial();
            //print_r($orden);        

            $totalPagado = 0;
            $totalGanancia = 0;
            foreach ($apuestas as $apuesta) {
                $ganador_temp = null;
                $ganador =null;
                $arreglo_jugadores = null;
                $propuesta = false;
                $detalles = $apuesta->getApuestaDetalles();
                $ganador_index = $apuesta->getTipo()->getId();

                foreach ($detalles as $detalle) {
                    $perfil = $detalle->getPerfil();
                    $arreglo_jugadores[] = $perfil;

                     // echo $perfil->getNickname().'<br/>';
                      $caballos = $detalle->getCaballos();                     
                      
                      /*$array = $detalle->getCaballos();                      
                      foreach ($array as $clave => $valor) {
                            echo "{$clave} => {$valor} </br>"; 

                       }*/

                    if($caballos){    
                     
                           for($i=0; $i < $apuesta->getTipo()->getId(); $i++){
                               if(in_array($orden[$i], $caballos)){                                
                                    if ($i<$ganador_index ) {
                                      $ganador = $perfil;
                                       $ganador_index = $i; 

                                    }                                 
                                    break;
                               }
                           }
                     }else{
                        $propuesta = true;  
                        $propuesta_perfil = $perfil; 
                     }                         

                }
                
                if($propuesta && !$ganador){
                   $ganador = $propuesta_perfil;
                } 

                if($ganador){                        
                    if($ganador->getPorcentajeGanar()!=NULL){
                         
                         $monto_porcentaje_ganador = $ganador->getPorcentajeGanar();
                    }else{
                        $monto_porcentaje_ganador=0;
                    }

                        $monto_porcentaje_2 = $apuesta->getMonto() *  ($monto_porcentaje_ganador/100);
                         $monto_porcentaje_1 = $apuesta->getMonto() -  $monto_porcentaje_2;
                       
                        //si el jugador que pierde tiene porcentaje de reintegro
                         //acomodar aqui lo de cuenta tambien
                        foreach($arreglo_jugadores as $jugador){
                         
                          if($ganador->getId() != $jugador->getId()){
                            $perdedor = $jugador;
                            $monto_porcentaje_jugador_perdedor = 0;
                            if($perdedor->getPorcentajePerder()>0){
                              $monto_porcentaje_jugador_perdedor = $apuesta->getMonto() *  ($perdedor->getPorcentajePerder()/100);                       
                                $monto_porcentaje_2 -= $monto_porcentaje_jugador_perdedor;
                                $perdedor->setSaldo( $perdedor->getSaldo() + $monto_porcentaje_jugador_perdedor);
                                //echo 'perdedor:'.($monto_porcentaje_jugador).'--';
                                $entityManager->persist($perdedor);
                            }          
                                
                          } 
                        }

                        //echo 'ganador:'.($apuesta->getMonto() + $monto_porcentaje_1).'--';
                        //echo 'casa:'.$monto_porcentaje_2.'--';
                        //exit;

                        //round() con dos decimales
                        $ganador->setSaldo($ganador->getSaldo() + $apuesta->getMonto() + $monto_porcentaje_1);

                        $apuesta->setGanador($ganador);

                        //hay que reestructurar esta entidad
                        $cuenta = new Cuenta();
                        $cuenta->setGerencia($user->getPerfil()->getGerencia());
                        $cuenta->setSaldoCasa($monto_porcentaje_2);
                        $cuenta->setSaldoGanador($monto_porcentaje_1);
                        $cuenta->setSaldoPerdedor($monto_porcentaje_jugador_perdedor);
                        //$cuenta->setSaldoSistema($monto_porcentaje_2);
                        $cuenta->setSaldoSistema(0);
                        $cuenta->setPerdedor($perdedor);
                        $cuenta->setGanador($ganador);

                        $apuesta->setCuenta($cuenta);

                        $saldo_casa_acumulado = $apuesta->getCarrera()->getGerencia()->getSaldoAcumulado();

                         $apuesta->getCarrera()->getGerencia()->setSaldoAcumulado($saldo_casa_acumulado + $monto_porcentaje_2 );
                        $entityManager->persist($apuesta);

                        $totalPagado += $monto_porcentaje_1+$monto_porcentaje_jugador_perdedor;
                        $totalGanancia += $monto_porcentaje_2;

              
                    
                        
                }else{
                    //echo '*******sin ganador, restaurar fondos*******';
                   
                    foreach ($detalles as $detalle) {
                        $perfil = $detalle->getPerfil();
                        $perfil->setSaldo($perfil->getSaldo() + $apuesta->getMonto());
                        //echo '///--'.$detalle->getPerfil()->getNickname();
                         $entityManager->persist($perfil);
                    }    
                }               

                //echo '*************************************<br/>';
            }
             
           $carrera->setTotalPagado($totalPagado);
           $carrera->setTotalGanancia($totalGanancia);
           $entityManager->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
            return $this->redirectToRoute('carrera_index');       
    }

    /**
     * @Route("/{id}", name="carrera_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Carrera $carrera): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carrera->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($carrera);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('carrera_index');
    }
 
}
