<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\AdjuntoPago;
use App\Entity\ApuestaDetalle;
use App\Entity\Traspaso;
use App\Entity\RetiroSaldo;
use App\Entity\PagoCliente;
use App\Entity\PagoPersonalSaldo;
use App\Entity\ApuestaPropuesta;
use App\Form\UserPassType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;


//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Require ROLE_USER for *every* controller method in this class.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("/", name="profile_show", methods={"GET","POST"})
     */
    public function show(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
       // usually you'll want to make sure the user is authenticated first       

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$this->getUser()->getPerfil()){
              $this->addFlash(
            'danger',
            'Debe seleccionar un perfil'
            );
            return $this->redirectToRoute('profile_update');
        }

        $user = $this->getUser();
   /********************change pass********************/     
        
        $form = $this->createForm(UserPassType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

           if ($form->isValid()) {
                    $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($password);                       
                    $this->getDoctrine()->getManager()->flush();
                    $this->addFlash(
                    'success',
                    'Clave cambiada con exito'
                    );
                    return $this->redirectToRoute('app_logout');
            }else{

                $this->addFlash(
                    'danger',
                    'Error: No se actualizó la clave'
                    );
            }
        }

         /*************************************************/          
        $i=0;
        $time=array();
        $apuestas = $this->getDoctrine()->getRepository(ApuestaDetalle::class)->findById15Dias($user->getPerfil()->getId());


            $tarjeta['gana'] = 0;
            $tarjeta['pierde'] = 0;
            $tarjeta['neutral'] = 0;
            $ext_observacion = null;
            
        foreach ($apuestas as $row) {
            $time[$i]['status_class'] = '';
            $time[$i]['monto'] =0;
            $time[$i]['tipo'] = 'apuesta';
            $time[$i]['status'] = $row->getApuesta()->getCarrera()->getStatus();
            $time[$i]['iclass'] = 'fas fa-horse-head bg-yellow';
            $time[$i]['fecha'] = $row->getApuesta()->getCarrera()->getFecha();
            
            if($row->getCaballos()){
                 $caballos = $row->getCaballos();
                 $ext_observacion = ' JUGO '; 
            }else{
                $apuesta_detalle = $this->getDoctrine()->getRepository(ApuestaDetalle::class)->findByApuestaCaballoNotNull($row->getApuesta()->getId());

                $caballos = $apuesta_detalle->getCaballos();
                $ext_observacion = ' PAGO ';                
                //$apuesta= $row->getApuesta();
            }

            $time[$i]['mensaje'] = $ext_observacion.$row->getApuesta()->getTipo()->getNombre().' en carrera '.$row->getApuesta()->getCarrera()->getNumeroCarrera().' de Hipódromo: '.$row->getApuesta()->getCarrera()->getHipodromo()->getNombre();

           


            $time[$i]['observacion'] = json_encode($caballos).' por '.$row->getApuesta()->getMonto();

          
            if($row->getApuesta()->getCarrera()->getStatus()=='PAGADO'){
                   $time[$i]['observacion'] .= ' - Orden oficial:'.json_encode($row->getApuesta()->getCarrera()->getOrdenOficial());
                    $time[$i]['iclass'] = 'fas fa-horse-head bg-blue';
                    $time[$i]['status_class'] = 'color:blue';

                    if($row->getApuesta()->getGanador()){
                        if($row->getApuesta()->getGanador()->getId()==$user->getPerfil()->getId()){
                             $time[$i]['monto'] = $row->getApuesta()->getCuenta()->getSaldoGanador();

                             $time[$i]['status'] = ''.$time[$i]['monto'];
                             $time[$i]['iclass'] = 'fas fa-horse-head bg-green';
                             $time[$i]['status_class'] = 'color:green';
                             $tarjeta['gana']++;

                        }else{
                            $time[$i]['monto'] = $row->getApuesta()->getMonto() - $row->getApuesta()->getCuenta()->getSaldoPerdedor();

                            //$time[$i]['status'] = 'PERDIO '.($time[$i]['monto']);
                            $time[$i]['status'] = ''.($time[$i]['monto']);
                            $time[$i]['monto'] = $time[$i]['monto']  * (-1);                            
                            
                            $time[$i]['iclass'] = 'fas fa-horse-head bg-red';
                            $time[$i]['status_class'] = 'color:red';
                            $tarjeta['pierde']++;


                        } 
                    }else{
                        $time[$i]['status'] = 'SIN GANADOR';
                        $tarjeta['neutral']++;
                    }
            }       
            $i++;  
        }

        /*$propuestas = $this->getDoctrine()->getRepository(ApuestaPropuesta::class)->findById15Dias($user->getPerfil()->getId());

          foreach ($propuestas as $row) {
            $time[$i]['tipo'] = 'propuesta';
            $time[$i]['status'] = '';
            $time[$i]['status_class'] = '';
            $time[$i]['iclass'] = 'fas fa-horse-head bg-purple';
            $time[$i]['fecha'] = $row->getUpdatedAt();
            $time[$i]['mensaje'] = 'Propuso '.$row->getTipo()->getNombre().' en carrera '.$row->getCarrera()->getNumeroCarrera().' de Hipódromo: '.$row->getCarrera()->getHipodromo()->getNombre();

            $time[$i]['observacion'] = json_encode($row->getCaballos()).' Restan: '.$row->getMonto();     
            $i++;  
        }*/



        $pagos = $this->getDoctrine()->getRepository(AdjuntoPago::class)->findById15Dias($user->getPerfil()->getId());    
        foreach ($pagos as $row) {
            $time[$i]['tipo'] = 'pago';
            $time[$i]['monto'] =0;
            
            if($row->getValidado()===NULL){
                $time[$i]['iclass'] = 'fas fa-dollar-sign bg-yellow';               
                $time[$i]['status'] = 'EN PROCESO';
                $time[$i]['status_class'] = 'color:blue';
            }else{
                
                if($row->getValidado()){
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                   $time[$i]['status_class'] = 'color:green';
                    $time[$i]['status'] = 'APROBADO';
                    $time[$i]['monto'] = $row->getMonto();
                }
                else{
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-red';
                   $time[$i]['status_class'] = 'color:red';
                   $time[$i]['status'] = 'RECHAZADO';
                }
            }

      
            
            $time[$i]['fecha'] = $row->getUpdatedAt();
            $time[$i]['mensaje'] = 'Su pago para abono de saldo con el numero de referencia '.$row->getNumeroReferencia().' se encuentra '.$time[$i]['status'];
            $time[$i]['observacion'] = $row->getObservacion();
            $i++;  
        }


        $retiros = $this->getDoctrine()->getRepository(RetiroSaldo::class)->findById15Dias($user->getPerfil()->getId());    
        foreach ($retiros as $row) {
            $time[$i]['tipo'] = 'retiro_saldo';
            $time[$i]['monto'] = 0;
            
            if($row->getValidado()===NULL){
                $time[$i]['iclass'] = 'fas fa-dollar-sign bg-yellow';               
                $time[$i]['status'] = 'EN PROCESO';
                $time[$i]['status_class'] = 'color:blue';

            }else{
                
                if($row->getValidado()){
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                   $time[$i]['status_class'] = 'color:green';
                    $time[$i]['status'] = 'APROBADO'; 
                    $ext_observacion  = ' bajo el numero de referencia '.$row->getNumeroReferencia();
                    $time[$i]['monto'] = $row->getMonto() * (-1);                  
                }
                else{
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-red';
                   $time[$i]['status_class'] = 'color:red';
                   $time[$i]['status'] = 'RECHAZADO';
                }
            }


            
            $time[$i]['fecha'] = $row->getUpdatedAt();
            $time[$i]['mensaje'] = 'Su solicitud de Retiro de saldo por '.$row->getMonto().' se encuentra '.$time[$i]['status'].$ext_observacion;
            $time[$i]['observacion'] = $row->getObservacion();
            $i++;  
        }

        $pagos_cliente = $this->getDoctrine()->getRepository(PagoCliente::class)->findById15Dias($user->getPerfil()->getId());    
        foreach ($pagos_cliente as $row) {
            $time[$i]['tipo'] = 'pago_cliente';
            $time[$i]['monto'] = $row->getMonto();
         
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                   $time[$i]['status_class'] = 'color:green';
                    $time[$i]['status'] = 'RETIRO';
        
      
            
            $time[$i]['fecha'] = $row->getUpdatedAt();
            $time[$i]['mensaje'] = 'Se realizo un deposito a favor por '.$row->getMonto().' con el numero de referencia '.$row->getNumeroReferencia().'  desde '.$row->getMetodoPago()->getNombre();
            $time[$i]['observacion'] = $row->getObservacion();
            $i++;  
        }    


        $pagos_personal_saldo = $this->getDoctrine()->getRepository(PagoPersonalSaldo::class)->findById15Dias($user->getPerfil()->getId());    
        foreach ($pagos_personal_saldo as $row) {
            $time[$i]['tipo'] = 'pago_personal_saldo';
            $time[$i]['monto'] =$row->getMonto();
         
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                   $time[$i]['status_class'] = 'color:green';
                   $time[$i]['status'] = 'ABONO DE SALDO';
        
                if($row->getMonto()>0){
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                   $time[$i]['status_class'] = 'color:green';
                    $time[$i]['status'] = $row->getConcepto();
                }
                else{
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-red';
                   $time[$i]['status_class'] = 'color:red';
                   $time[$i]['status'] = $row->getConcepto();
                }
            
            $time[$i]['fecha'] = $row->getUpdatedAt();
            $time[$i]['mensaje'] = 'Se realizo un movimiento de saldo por '.$row->getMonto(). ' por concepto de '.$row->getConcepto();
            $time[$i]['observacion'] = $row->getObservacion();
            $i++;  
        }                

        $traspasos = $this->getDoctrine()->getRepository(Traspaso::class)->findById15Dias($user->getPerfil()->getId());    
        foreach ($traspasos as $row) {
            $time[$i]['tipo'] = 'traspaso';
            $time[$i]['monto'] =$row->getMonto();  
       
                
                if($row->getDescuento()->getId() != $user->getPerfil()->getId()){
                    $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';
                    $time[$i]['status'] = 'ABONO';
                    $time[$i]['mensaje'] = 'ABONO del usuario '.$row->getDescuento()->getNickname(). ' por '.$row->getMonto();
                    $time[$i]['status_class'] = 'color:green';

                }
                else{
                   $time[$i]['iclass'] = 'fas fa-dollar-sign bg-red';
                   $time[$i]['status'] = 'DESCUENTO';
                   $time[$i]['status_class'] = 'color:red';
                   $time[$i]['mensaje'] = 'TRASPASO hacia el usuario '.$row->getAbono()->getNickname(). ' por '.$row->getMonto();
                   $time[$i]['monto'] = $time[$i]['monto'] * (-1);
                }      
            
            $time[$i]['fecha'] = $row->getUpdatedAt();
            
            $time[$i]['observacion'] = $row->getObservacion();
            $i++; 

        }        

        //burbuja
         $longitud = count($time);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($time[$j]['fecha'] < $time[$j + 1]['fecha']) {
                        $temporal = $time[$j];
                        $time[$j] = $time[$j + 1];
                        $time[$j + 1] = $temporal;
                    }
                }
            }


        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'times' => $time,
            'tarjeta'=>$tarjeta,
            'form' => $form->createView(),                     
        ]);
    }

    /**
     * @Route("/update", name="profile_update", methods={"GET","POST"})
     */
    public function update(Request $request): Response
    {

                // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); 

      
        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        //return new Response('Well hi there '.$user->getPerfil()->getNombre());
        return $this->render('profile/update.html.twig', [
            'user' => $user,
            'perfils' => $user->getPerfils(),
           
        ]);
    } 

    /**
     * @Route("/new", name="profile_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfil = new Perfil();
       
        $user = $this->getUser(); 
        $perfil->setUsuario($user);

        $form = $this->createForm(ProfileType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $perfil->setActivo(false);            

            $entityManager->persist($perfil);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('profile_update');
        }

        return $this->render('profile/new.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }   

    /**
     * @Route("/{id}/change", name="profile_change", methods={"GET","POST"})
     */
    public function change(Request $request, Perfil $perfil): Response
    {

            if($perfil->getActivo()){
                $entityManager = $this->getDoctrine()->getManager();         
                $perfil->getUsuario()->setPerfil($perfil); 
                $perfil->getUsuario()->setRoles($perfil->getRoles());

                //$perfil->getUsuario();
                $entityManager->persist($perfil);
                $entityManager->flush();

               $this->addFlash(
                'success',
                'Perfil cambiado!'
                );
                return $this->redirectToRoute('app_logout');
            }


                $this->addFlash(
                'warning',
                'El perfil debe ser activado por un administrador'
                );
                return $this->redirectToRoute('profile_update');
           
       
    }

    /**
     * @Route("/api/ProfileSearch", name="profile_search", methods={"GET","POST"})
     */
    public function apiSearch(Request $request): Response
    {

          $user = $this->getUser(); 
          $perfiles = array();
          
          //echo '---66------'.$request->get("phrase").$user->getGerencia()->getId();

			
	          $perfils = $this->getDoctrine()->getRepository(Perfil::class)->findByGerencia($user->getPerfil()->getGerencia()->getId());

	          $i = 0;
	          foreach($perfils as $perfil){
	          	$perfiles[$i]['id'] = $perfil->getId();
	          	$perfiles[$i]['nickname'] = $perfil->getNickname();
	          	$perfiles[$i]['saldo'] = $perfil->getSaldo();

	          	$perfiles[$i]['nombre'] = $perfil->getUsuario()->getNombre();
	          	$perfiles[$i]['apellido'] = $perfil->getUsuario()->getApellido();
	          	$i++;
	          }
	      	
	      /*  echo '<pre>';
	        	print_r($perfiles);
	        echo '</pre>';	         
          exit;*/
          


        $response = new JsonResponse();
		    $response->setData($perfiles);

          //$response->setData([0=>['nombre' =>  $user->getNombre(), 'message' => 'status_comlpetado_200', 'code' => Response::HTTP_OK]]);

        return $response;
    }

    /**
     * @Route("/prueba", name="profile_prueba", methods={"GET","POST"})
     */
    /*public function prueba(ChatterInterface $chatter)
    {

       $chatMessage = new ChatMessage('');

        // Create Telegram options
        $telegramOptions = (new TelegramOptions())
            ->chatId('@symfony66')
            ->parseMode('MarkdownV2')
            ->disableWebPagePreview(true)
            ->disableNotification(true)
            ->replyMarkup((new InlineKeyboardMarkup())
                ->inlineKeyboard([
                    (new InlineKeyboardButton('Visit symfony.com'))
                        ->url('https://symfony.com/'),
                ])
            );

        // Add the custom options to the chat message and send the message
        $chatMessage->options($telegramOptions);

        $chatter->send($chatMessage);

           $this->addFlash(
            'success',
            'mensaje enviado'
            );

            return $this->redirectToRoute('profile_show');
       
    }*/        

}
