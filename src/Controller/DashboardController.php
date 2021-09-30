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


//pdf
use Dompdf\Dompdf;
use Dompdf\Options;



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
        $hipodromo = null; //graph
        $i = 0;
    	foreach($carreras as $carrera){
    		$apuestas = $carrera->getApuestas();
    		$cantidad_apuestas += count($apuestas);

    		foreach($apuestas as $apuesta){
	    		$correcciones = $apuesta->getCorreccions();
	    		$cantidad_correcciones += count($correcciones);
	    	} 

            $hipodromo['conteo'][$carrera->getHipodromo()->getId()] = $i+1;
            $hipodromo['nombre'][$carrera->getHipodromo()->getId()]=$carrera->getHipodromo()->getNombre(); 	
    		$i++;
    	}
    	 	
    	$tarjeta['apuestas'] = $cantidad_apuestas;
        $tarjeta['saldo'] = $gerencia->getSaldoAcumulado();
    	$tarjeta['correcciones'] = $cantidad_correcciones;

        //mes - gerencia - validado

        //retiro_saldo
        $parametros = NULL;
        $parametros['gerencia_id'] = $gerencia->getId();
        $parametros['validado'] = true;
        foreach ($meses as $mes) {
            $parametros['mes'] = $mes;
            $retiro = $this->getDoctrine()->getRepository(RetiroSaldo::class)->findByMesGerenciaValidado($parametros);
            
            if($retiro['suma']){
                $dinero['retiro'][] = intval($retiro['suma']);
            }else{
                $dinero['retiro'][] = 0;
            }
                
        }

        //adjunto_pago
        $parametros = NULL;
        $parametros['gerencia_id'] = $gerencia->getId();
        $parametros['validado'] = true;
        foreach ($meses as $mes) {
            $parametros['mes'] = $mes;
            $retiro = $this->getDoctrine()->getRepository(AdjuntoPago::class)->findByMesGerenciaValidado($parametros);
            
            if($retiro['suma']){
                $dinero['adjunto_pago'][] = intval($retiro['suma']);
            }else{
                $dinero['adjunto_pago'][] = 0;
            }
                
        }

            /*echo '<pre>';
                print_r($dinero);
            echo '</pre>';*/
           
           

  
        //exit;

        return $this->render('dashboard/dash1.html.twig', [
            'tarjeta' => $tarjeta,
            'hipodromo' => $hipodromo,
            'dinero' => $dinero
        ]);
    }


    
}
