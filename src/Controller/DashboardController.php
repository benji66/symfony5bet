<?php

namespace App\Controller;

use App\Entity\Carrera;

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
		$cantidad_clientes = $gerencia->getPerfils()->count();
    	$tarjeta['clientes'] = $cantidad_clientes;
    	
    	$carreras = $this->getDoctrine()->getRepository(Carrera::class)->findByGerenciaWeeks($gerencia->getId());

    	$tarjeta['carreras'] = count($carreras);
		
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
    	//echo count($carreras).'--/--';
    	//exit;
        return $this->render('dashboard/dash1.html.twig', [
            'tarjeta' => $tarjeta,
            'hipodromo' => $hipodromo,
        ]);
    }


    
}
