<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\RetiroSaldo;
use App\Entity\AdjuntoPago;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



//email
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @IsGranted("ROLE_GERENCIA")
     * @Route("/", name="dashboard_index")
     */
    public function index()
    {      

    	$gerencia = $this->getUser()->getPerfil()->getGerencia();    
		

        $meses = ['January','February','March','April','May','June','July','August','September', 'October','November', 'December'];



        $cantidad_clientes = $gerencia->getPerfils()->count();
    	$tarjeta['clientes'] = $cantidad_clientes;
    	
    	$carreras = $this->getDoctrine()->getRepository(Carrera::class)->findByGerenciaWeeks($gerencia->getId());

    	$tarjeta['carreras'] = count($carreras);
		
		
        //id="pieChart"

        $cantidad_apuestas = 0;
		$cantidad_correcciones = 0;
        $ganadores=  array();
        $contador= array();
       
        $i = 0;
    	foreach($carreras as $carrera){
    		$apuestas = $carrera->getApuestas();
    		$cantidad_apuestas += count($apuestas);

    		foreach($apuestas as $apuesta){
	    		$correcciones = $apuesta->getCorreccions();
	    		$cantidad_correcciones += count($correcciones);
                
                if($apuesta->getGanador()){
                    if(!isset($ganadores[$apuesta->getGanador()->getId()]['conteo'])){
                        $ganadores[$apuesta->getGanador()->getId()]['conteo']=0;
                    }   

                    if(!isset($ganadores[$apuesta->getGanador()->getId()]['monto'])){
                        $ganadores[$apuesta->getGanador()->getId()]['monto']=0;
                    }                   
                    
                    $ganadores[$apuesta->getGanador()->getId()]['id'] = $apuesta->getGanador()->getId();
                    $ganadores[$apuesta->getGanador()->getId()]['nickname'] = $apuesta->getGanador()->getNickname();
                    $ganadores[$apuesta->getGanador()->getId()]['conteo'] += 1;
                    $ganadores[$apuesta->getGanador()->getId()]['monto'] += $apuesta->getMonto();                     
                   
                }
                
	    	}
         
    	}
/******************************************************************************************************/
         //ordenar los indices del arreglo
         $ganadores_temp = array();
         foreach ($ganadores as $item ) {
             $ganadores_temp[] = $item; 
         }

         $ganadores = $ganadores_temp;

         //ordenarlos burbuja y monto
         $longitud = count($ganadores);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($ganadores[$j]['monto'] < $ganadores[$j + 1]['monto']) {
                        $temporal = $ganadores[$j];
                        $ganadores[$j] = $ganadores[$j + 1];
                        $ganadores[$j + 1] = $temporal;
                    }
                }
            }

        $grafico_dona['label'][0] = null;
        $grafico_dona['data'][0] = null;
          // 5 por monto
        for($i=0;$i<=4; $i++){
           if(isset($ganadores[$i])){
               $grafico_dona['label'][$i] = $ganadores[$i]['nickname'];
                $grafico_dona['data'][$i] = $ganadores[$i]['monto'];
           }          
        }



         //ordenarlos burbuja y cantidad de apuestas
         $longitud = count($ganadores);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($ganadores[$j]['conteo'] < $ganadores[$j + 1]['conteo']) {
                        $temporal = $ganadores[$j];
                        $ganadores[$j] = $ganadores[$j + 1];
                        $ganadores[$j + 1] = $temporal;
                    }
                }
            }

        $grafico_torta['label'][0] = null;
        $grafico_torta['data'][0] = null;
          // 5 por cantidad de apuesta
        for($i=0;$i<=4; $i++){
           if(isset($ganadores[$i])){
               $grafico_torta['label'][$i] = $ganadores[$i]['nickname'];
                $grafico_torta['data'][$i] = $ganadores[$i]['conteo'];
           }          
        }
        
        /****************************************************************************************/  
    	 	
    	$tarjeta['apuestas'] = $cantidad_apuestas;
        $tarjeta['saldo'] = $gerencia->getSaldoAcumulado();
    	$tarjeta['correcciones'] = $cantidad_correcciones;

        //mes - gerencia - validado

      
        $parametros = NULL;
        $parametros['gerencia_id'] = $gerencia->getId();
        $parametros['validado'] = true;
        foreach ($meses as $mes) {
            $parametros['mes'] = $mes;
            
           //retiro_saldo
            $retiro = $this->getDoctrine()->getRepository(RetiroSaldo::class)->findByMesGerenciaValidado($parametros);
            
            if($retiro['suma']){
                $dinero['retiro'][] = intval($retiro['suma']);
            }else{
                $dinero['retiro'][] = 0;
            }

            
            //abonos saldo
            $abono = $this->getDoctrine()->getRepository(AdjuntoPago::class)->findByMesGerenciaValidado($parametros);
            
            if($abono['suma']){
                $dinero['adjunto_pago'][] = intval($abono['suma']);
            }else{
                $dinero['adjunto_pago'][] = 0;
            }
                
        }


     /*      echo '<pre>';
                //print_r($tarjeta);
                print_r($ganadores);
                //print_r($dinero);
            echo '</pre>';
           
           

  
        exit;*/


       /*   echo '<pre>';
                //print_r($tarjeta);
                print_r(json_encode($grafico_dona));
                //print_r($dinero);
            echo '</pre>';
           
           

  
        exit;*/

        return $this->render('dashboard/dash2.html.twig', [
            'tarjeta' => $tarjeta,
            'dona' => $grafico_dona,
            'torta' => $grafico_torta,
            'dinero' => $dinero
        ]);
    }

    /**
     * @Route("/email")
     */
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('no-reply@raul-gimenez.com')
            ->to('benji.gc@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

           $mailer->send($email);

        // ...
    }

    
}
