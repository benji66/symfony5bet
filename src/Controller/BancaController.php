<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Banca;
use App\Entity\ApuestaTipo;
use App\Entity\Perfil;
use App\Entity\Apuesta;
use App\Entity\ApuestaDetalle;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\BancaType;
use App\Form\BancaConfirmarType;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/banca")
 */
class BancaController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('banca/index.html.twig', [
            'controller_name' => 'BancaController',
        ]);
    }

   /**
     * @Route("/{id}/new", name="banca_new", methods={"POST","GET"})
     */
    public function new(Request $request, Carrera $carrera): Response
    {

    	   $perfil_logueado = $this->getUser()->getPerfil();
          $gerencia_logueada = $perfil_logueado->getGerencia()->getId();
          $gerencia = $carrera->getGerencia()->getId();          

            if($gerencia_logueada != $gerencia){
                $this->addFlash(
                'danger',
                'Acceso no autorizado'
                );
                return $this->redirectToRoute('carrera_index');  
            }    

           if($carrera->getStatus()=="PAGADO"){

                $this->addFlash(
                'danger',
                'Operacion no permitida, status '.$carrera->getStatus()
                );
                return $this->redirectToRoute('carrera_index');  
            }



        $banca = new Banca();
        $form = $this->createForm(BancaType::class, $banca);
        $form->handleRequest($request);

        $parametros['perfil_id'] = $perfil_logueado->getId();
        $bancas = $this->getDoctrine()->getRepository(Banca::class)->findByUsuario($parametros);

     

         if ($form->isSubmitted() && $form->isValid()) {

            $error=false;
            $banca->setCarrera($carrera);
            $banca->setUsuario($perfil_logueado);

                $sw = false;
                 foreach ($bancas as $row){
                       if($row->getCliente() == $banca->getCliente()){
                            $this->addFlash(
                                'danger',
                                $banca->getCliente()->getNickname().' fue agregado previamente, revisar listado' 
                             );
                            $error=true;
                       } 

                        /*if($row->getTipo() != $banca->getTipo() && $sw==false){
                            $this->addFlash(
                                'danger',
                                'El tipo de apuesta debe coincidir con '.$row->getTipo()->getNombre().' ('.$banca->getTipo()->getNombre().')'
                             );
                            $error=true;
                            $sw=true;                            
                       }*/ 
                 } 


                if($banca->getMonto() < 1){
                       $this->addFlash(
                        'danger',
                        $banca->getCliente()->getNickname().' error en el monto ('.$banca->getMonto().')'
                        );
                    $error=true;
                }


                 
                 if(($banca->getCliente()->getSaldo() >= $banca->getMonto()) || ($banca->getCliente()->getSaldoIlimitado())){
                      $banca->getCliente()->setSaldo($banca->getCliente()->getSaldo() -  $banca->getMonto());                
                   
                 }else{                    
                     $this->addFlash(
                        'danger',
                        $banca->getCliente()->getNickname().' no posee saldo suficiente ('.$banca->getCliente()->getSaldo().')'
                        );
                      $error=true;
                 }                 
          
//validar los que les falta saldo, referencia ApuestaController
            if(!$error){     

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($banca);        
                    $entityManager->flush();

                    $bancas = $this->getDoctrine()->getRepository(Banca::class)->findByUsuario($parametros);        

                     $this->addFlash(
                    'success',
                    'Los cambios fueron realizados!'
                    );
              }   

         }   

      $formConfirmar = $this->createForm(BancaConfirmarType::class, array('message'=>''));

    	return $this->render('banca/new.html.twig', [
            'controller_name' => 'BancaController NEW',
            'form' => $form->createView(),
            'form_confirmar' => $formConfirmar->createView(),
            'carrera' => $carrera,
            'bancas' => $bancas,
        ]);
    } 


    /**
     * @Route("/{id}", name="banca_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Banca $banca): Response
    {
        if ($this->isCsrfTokenValid('delete'.$banca->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $banca->getCliente()->setSaldo($banca->getCliente()->getSaldo() + $banca->getMonto());
            $entityManager->persist($banca);                  
            
            $entityManager->remove($banca);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('banca_new', ['id'=>$banca->getCarrera()->getId()]);
    } 


    /**
     * @Route("/cargar", name="banca_cargar", methods={"POST"})
     */
    public function cargar(Request $request): Response
    {       
        
        /*$gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $propuesta->getCarrera()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){
            $this->addFlash(
            'danger',
            'Acceso no autorizado'
            );
            return $this->redirectToRoute('carrera_abierta_index');
        }    

       if($propuesta->getCarrera()->getStatus()!='ABIERTO'){
          $this->addFlash(
                   'danger',
                   'La carrera se encuentra cerrada'
              );
           return $this->redirectToRoute('carrera_abierta_index');
        }*/
        //crf token
        
        $perfil = $this->getUser()->getPerfil();
        if ($this->isCsrfTokenValid('cargar'.$perfil->getId(), $request->request->get('_token'))) {
                

                $parametros['perfil_id'] = $perfil->getId();
                $parametros['juega'] = true;
                $bancas_juega = $this->getDoctrine()->getRepository(Banca::class)->findByUsuarioJuega($parametros);

                $i=0;
                foreach($bancas_juega as $banca){
                   $matriz_juega[$i]['cliente_id'] = $banca->getCliente()->getId();
                   $matriz_juega[$i]['banca_id'] = $banca->getId();
                   $matriz_juega[$i]['monto'] = $banca->getMonto();
                   $matriz_juega[$i]['carrera_id'] = $banca->getCarrera()->getId();
                   $i++;
                }


                $parametros['juega'] = false;
                $bancas_da = $this->getDoctrine()->getRepository(Banca::class)->findByUsuarioJuega($parametros);

                $i=0;
                foreach($bancas_da as $banca){
                   $matriz_da[$i]['cliente_id'] = $banca->getCliente()->getId();
                   $matriz_da[$i]['banca_id'] = $banca->getId();
                   $matriz_da[$i]['monto'] = $banca->getMonto();
                   $matriz_da[$i]['carrera_id'] = $banca->getCarrera()->getId();
                   $i++;
                }

           /* echo '<pre>';
                print_r($matriz_juega);
                print_r($matriz_da);
            echo '</pre>';*/
            //matriz de apuestas individuales en base a los datos arreglados
            $i=0;
            foreach ($matriz_juega as $cliente_juega) {               
               while ($cliente_juega['monto'] > 0) {
                    for($j=0; $j<count($matriz_da); $j++) { 

                       if($matriz_da[$j]['monto']>0){
                            if($cliente_juega['monto'] > $matriz_da[$j]['monto']){
                                
                                $matriz_apuesta[$i]['monto'] = $matriz_da[$j]['monto'];                           
                                $cliente_juega['monto'] -= $matriz_da[$j]['monto'];
                                
                                //echo $i.'***//'.$matriz_da[$j]['monto'].'--'.$matriz_da[$j]['cliente_id'].'-a-'.'<br/>';
                                $matriz_da[$j]['monto'] = 0;

                                $matriz_apuesta[$i]['banca_id'] = $matriz_da[$j]['banca_id'];
                                $matriz_apuesta[$i]['carrera_id'] = $matriz_da[$j]['carrera_id'];
                                $matriz_apuesta[$i]['cliente_da'] = $matriz_da[$j]['cliente_id'];
                                $matriz_apuesta[$i]['cliente_juega'] = $cliente_juega['cliente_id'];

                                $i++;                                                          

                            //esta condicion esta demas. deberia ser solo else.  
                            }elseif($cliente_juega['monto'] <= $matriz_da[$j]['monto']) {                              
                                $matriz_apuesta[$i]['monto'] = $cliente_juega['monto'];                                
                                $matriz_da[$j]['monto'] -= $cliente_juega['monto'];                                
                                //echo $i.'*//'.$matriz_da[$j]['monto'].'--'.$matriz_da[$j]['cliente_id'].'-a-'.'<br/>';
                                $cliente_juega['monto'] = 0;
                                
                                $matriz_apuesta[$i]['carrera_id'] = $matriz_da[$j]['carrera_id'];
                                 $matriz_apuesta[$i]['banca_id'] = $cliente_juega['banca_id'];
                                $matriz_apuesta[$i]['cliente_da'] = $matriz_da[$j]['cliente_id'];
                                $matriz_apuesta[$i]['cliente_juega'] = $cliente_juega['cliente_id'];


                                $i++;
                                break;                               
                            } 

                           
                        }                   

                    }
                }

            }    
           // echo '-------carga apuestas-------';

            $caballos = $request->get('caballos');
            $tipo = $request->get('banca_confirmar'); 
           /* echo '<pre>';
                print_r($matriz_apuesta);
                print_r($caballos);
                print_r($tipo['tipo']);
   
            echo '</pre>';
            exit;   */

            $tipo = $this->getDoctrine()->getRepository(ApuestaTipo::class)->find($tipo['tipo']);
            
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($matriz_apuesta as $apuesta) {

            /*   echo '<pre>';
               // print_r($matriz_apuesta);
                //print_r($caballos);
                //print_r($tipo['tipo']);
                print_r($apuesta);   
            echo '</pre>';
            exit();
            */
                 $perfil_juega = $this->getDoctrine()->getRepository(Perfil::class)->find($apuesta['cliente_juega']);
                 $perfil_da = $this->getDoctrine()->getRepository(Perfil::class)->find($apuesta['cliente_da']);     
                 $banca = $this->getDoctrine()->getRepository(Banca::class)->find($apuesta['banca_id']);
                # code...
                $apuesta_entity = new Apuesta();                
                $apuesta_entity->setMonto($apuesta['monto']);
                $apuesta_entity->setTipo($tipo);
                $apuesta_entity->setCarrera($banca->getCarrera());              
                //el que juega
                $apuesta_detalle = new ApuestaDetalle();
                $apuesta_detalle->setPerfil($perfil_juega);
       //---ojo
                $apuesta_detalle->setCaballos($caballos);
                $apuesta_entity->addApuestaDetalle($apuesta_detalle);

                //-quien apuesta, caballos en null ya que se rigen por los caballso propuestos 
                //el que da               
                $apuesta_detalle = new ApuestaDetalle();
                $apuesta_detalle->setPerfil($perfil_da);
                $apuesta_detalle->setCaballos(null);
                $apuesta_entity->addApuestaDetalle($apuesta_detalle);
                
         
                $banca->getCarrera()->addApuesta($apuesta_entity);
                 //solo esta actualizando da
                
                $entityManager->persist($banca);        
                 
            }

            foreach($bancas_juega as $banca){
                $banca->setProcesadoAt(\DateTime::createFromFormat('Y-m-d', date('Y-m-d'))  );
                $entityManager->persist($banca); 
            }
            foreach($bancas_da as $banca){
                $banca->setProcesadoAt(\DateTime::createFromFormat('Y-m-d', date('Y-m-d'))  );
                $entityManager->persist($banca); 
            }

            $entityManager->flush();            
            $this->addFlash(
                   'success',
                   'Apuestas cargadas'
              );
   
            return $this->redirectToRoute('banca_new', ['id'=>$banca->getCarrera()->getId()]);
        }        
    }     


}
