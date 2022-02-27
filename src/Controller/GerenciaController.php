<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\Gerencia;
use App\Entity\Apuesta;
use App\Form\GerenciaType;
use App\Repository\GerenciaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/gerencia")
 */
class GerenciaController extends AbstractController
{
    /**
     * @Route("/", name="gerencia_index", methods={"GET"})
     */
    public function index(GerenciaRepository $gerenciaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        //searchForm
        $gerencium = new Gerencia();
        $form = $this->createForm(GerenciaType::class, $gerencium);
        
        
        $allRowsQuery = $gerenciaRepository->createQueryBuilder('a')
            //->where('a.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        //example filter code, you must uncomment and modify    

        if ($request->query->get("gerencia")) {
            $val = $request->query->get("gerencia");
          
            $gerencium->setnombre($val['nombre']);
            
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.nombre LIKE :nombre')
            ->setParameter('nombre', '%'.$val['nombre'].'%');
        }

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
        return $this->render('gerencia/index.html.twig', [
            'gerencias' => $rows,
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="gerencia_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $gerencium = new Gerencia();
        $form = $this->createForm(GerenciaType::class, $gerencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($gerencium);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('gerencia_index');
        }

        return $this->render('gerencia/new.html.twig', [
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gerencia_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Gerencia $gerencium): Response
    {
        return $this->render('gerencia/show.html.twig', [
            'gerencium' => $gerencium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gerencia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Gerencia $gerencium): Response
    {
        $form = $this->createForm(GerenciaType::class, $gerencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

           $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('gerencia_index');
        }

        return $this->render('gerencia/edit.html.twig', [
            'gerencium' => $gerencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gerencia_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Gerencia $gerencium): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gerencium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($gerencium);
            $entityManager->flush();
         
         $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }

        return $this->redirectToRoute('gerencia_index');
    }


    /**
     * @Route("/reporte_usuarios_saldo", name="gerencia_reporte_usuarios_saldo", methods={"GET"})
     */
    public function reporte_usuarios_saldo(Request $request): Response
    {           
 
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
   
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia();
        $gerencia_logueada_id = $this->getUser()->getPerfil()->getGerencia()->getId();
     



        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(Apuesta::class); 


        $allRowsQuery = $repository->createQueryBuilder('a'); 
        $queryTotalSemana = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify
        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 

            $rango = ['desde'=>$fecha1, 'hasta'=>$fecha2]; 
          
                        
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c')           
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')   
            ->andWhere('c.gerencia = :gerencia')            
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)           
            ->setParameter('gerencia', $gerencia_logueada);

        }


        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();
        $matriz = null;
        $matriz_total = array();
        $total_ganancia = 0;
        foreach ($allRowsQuery as $row){          
  
          $perdedor_id =  $row->getCuenta()->getPerdedor()->getId();
          $ganador_id =  $row->getCuenta()->getGanador()->getId();

            
            $matriz[$ganador_id]['nombre'] = $row->getCuenta()->getGanador()->getUsuario()->getNombre();
            $matriz[$ganador_id]['nickname'] = $row->getCuenta()->getGanador()->getNickname(); 
            $matriz[$perdedor_id]['nombre'] = $row->getCuenta()->getPerdedor()->getUsuario()->getNombre();
            $matriz[$perdedor_id]['nickname'] = $row->getCuenta()->getPerdedor()->getNickname();

           

            if(!isset($matriz[$ganador_id]['monto_ganador'])){
                              $matriz[$ganador_id]['monto_ganador'] = $row->getCuenta()->getSaldoGanador();               
            }else{                        
                 $matriz[$ganador_id]['monto_ganador'] += $row->getCuenta()->getSaldoGanador();               
            }

            if(!isset($matriz[$perdedor_id]['monto_perdedor'])){
                $matriz[$perdedor_id]['monto_perdedor'] = ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);

            }else{
                  $matriz[$perdedor_id]['monto_perdedor'] += ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);              

            }

           $total_ganancia += $row->getCuenta()->getSaldoCasa(); 

        }

      if(!isset($matriz)){
         $this->addFlash(
             'danger',
             'No hay registros'
            );
            
            return $this->redirectToRoute('reporte_index');
            
       }   
                $i=0;
                      $matriz_xls = NULL;
                       $matriz_xls = array();  
                      foreach($matriz as $item){
                        $total_ganador = isset($item['monto_ganador']) ? $item['monto_ganador'] : 0;
                        $total_perdedor = isset($item['monto_perdedor']) ? $item['monto_perdedor'] : 0;
                        
                        //echo $item['nombre'].'-'.$total_ganador .'-'.$total_perdedor.'='.($total_ganador - $total_perdedor).'<br>';

                        $matriz_xls[$i]['total'] = $total_ganador + $total_perdedor;
                        $matriz_xls[$i]['nombre'] = $item['nombre'];
                        $matriz_xls[$i]['nickname'] = $item['nickname'];
                       
                        $i++;
                      }

  

  
                     //burbuja
                     $longitud = count($matriz_xls);
                        for ($i = 0; $i < $longitud; $i++) {
                            for ($j = 0; $j < $longitud - 1; $j++) {
                                if ($matriz_xls[$j]['nickname'] > $matriz_xls[$j + 1]['nickname']) {
                                    $temporal = $matriz_xls[$j];
                                    $matriz_xls[$j] = $matriz_xls[$j + 1];
                                    $matriz_xls[$j + 1] = $temporal;
                                }
                            }
                        }
  
  /*     echo "<pre>";
          //print_r($mat);
          //print_r($matriz);
          print_r($matriz_xls);
          print_r($total_ganancia);
          
       echo "</pre>";

        exit;
*/
if($request->query->get("vista")){ 
         return $this->render('gerencia/reporte_usuarios_saldo.html.twig', [
            'ganancia' => $total_ganancia,       
             'gerencia' => $gerencia_logueada,
             'totales' => $matriz_xls,
             'rango'=>$rango         
        ]);
 }     
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('gerencia/reporte_usuarios_saldo.html.twig', [
             'ganancia' => $total_ganancia,       
             'gerencia' => $gerencia_logueada,
             'totales' => $matriz_xls,
             'rango'=>$rango
        ]);
    

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'landscape' or 'portrait'
        $dompdf->setPaper('A4', 'portrait'); //landscape

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("reporte_usuarios_saldo.pdf", [
            "Attachment" => true
        ]);             

          echo 'lanffza';
        exit;
    }   



    /**
     * @Route("/reporte_semana_saldo", name="gerencia_reporte_semana_saldo", methods={"GET"})
     */
    public function reporte_semana_saldo(Request $request): Response
    {           
 
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
   
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia();
        $gerencia_logueada_id = $this->getUser()->getPerfil()->getGerencia()->getId();

        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(Apuesta::class); 


        $allRowsQuery = $repository->createQueryBuilder('a'); 
        $queryTotalSemana = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify
        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 


            $week_number = date('W', strtotime($fecha2));
            $year = date('Y', strtotime($fecha2));
            /*for($day=1; $day<=7; $day++)
            {
                echo date('m/d/Y', strtotime($year."W".$week_number.$day))."\n";
            }*/

            $week_first =  date('Y-m-d', strtotime($year."W".$week_number.(1) ));
            $week_last =  date('Y-m-d', strtotime($year."W".$week_number.(7) ));

            $rango = ['desde'=>$week_first, 'hasta'=>$week_last]; 
           // echo $week_first.' ------ '.$week_last;
           //exit;             
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c')           
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')   
            ->andWhere('c.gerencia = :gerencia')            
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $week_first)
            ->setParameter('fecha2', $week_last)           
            ->setParameter('gerencia', $gerencia_logueada);

        }



        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();
        $matriz = null;
        $matriz_total = array();
        $total_ganancia = 0;
        foreach ($allRowsQuery as $row){          
  
          $perdedor_id =  $row->getCuenta()->getPerdedor()->getId();
          $ganador_id =  $row->getCuenta()->getGanador()->getId();

            $fecha_carrera =  date('l', strtotime($row->getCarrera()->getFecha()->format('Y-m-d')));

            $matriz[$ganador_id]['nombre'] = $row->getCuenta()->getGanador()->getUsuario()->getNombre();
            $matriz[$ganador_id]['nickname'] = $row->getCuenta()->getGanador()->getNickname(); 
            $matriz[$perdedor_id]['nombre'] = $row->getCuenta()->getPerdedor()->getUsuario()->getNombre();
            $matriz[$perdedor_id]['nickname'] = $row->getCuenta()->getPerdedor()->getNickname();

           

            if(!isset($matriz[$ganador_id]['dias'][$fecha_carrera]['monto_ganador'])){
                 $matriz[$ganador_id]['dias'][$fecha_carrera]['monto_ganador'] = $row->getCuenta()->getSaldoGanador();               
            }else{                        
                 $matriz[$ganador_id]['dias'][$fecha_carrera]['monto_ganador'] += $row->getCuenta()->getSaldoGanador();               
            }

            if(!isset($matriz[$perdedor_id]['dias'][$fecha_carrera]['monto_perdedor'])){
               $matriz[$perdedor_id]['dias'][$fecha_carrera]['monto_perdedor'] = ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);

            }else{
                $matriz[$perdedor_id]['dias'][$fecha_carrera]['monto_perdedor'] += ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);             
            }

            if(isset($matriz_com[$fecha_carrera])){
               $matriz_com[$fecha_carrera] += $row->getCuenta()->getSaldoCasa();                          
            }else{
                $matriz_com[$fecha_carrera] = $row->getCuenta()->getSaldoCasa();                
            }

            if(isset($matriz_com['total'])){
               $matriz_com['total'] += $row->getCuenta()->getSaldoCasa();                          
            }else{
                $matriz_com['total'] = $row->getCuenta()->getSaldoCasa();                
            }

 

        }
           
      if(!isset($matriz)){
         $this->addFlash(
             'danger',
             'No hay registros en el rango seleccionado'
            );
            
            return $this->redirectToRoute('reporte_index');
            
      }      
                   $i=0;
                      $matriz_xls = NULL;
                       $matriz_xls = array();  
                     foreach($matriz as $item){
                        
                        $total_ganador = isset($item['monto_ganador']) ? $item['monto_ganador'] : 0;
                        $total_perdedor = isset($item['monto_perdedor']) ? $item['monto_perdedor'] : 0;
                        $matriz_xls[$i]['total'] = 0;

                        foreach ($item['dias'] as $key => $dia) {
                            $total_ganador = isset($dia['monto_ganador']) ? $dia['monto_ganador'] : 0;
                            $total_perdedor = isset($dia['monto_perdedor']) ? $dia['monto_perdedor'] : 0;

                            $matriz_xls[$i][$key] = $total_ganador + $total_perdedor;
                            $matriz_xls[$i]['total'] += $total_ganador + $total_perdedor;

                        }
                        

                        //echo $item['nombre'].'-'.$total_ganador .'-'.$total_perdedor.'='.($total_ganador - $total_perdedor).'<br>';

                        //$matriz_xls[$i]['total'] = $total_ganador + $total_perdedor;
                        $matriz_xls[$i]['nombre'] = $item['nombre'];
                        $matriz_xls[$i]['nickname'] = $item['nickname'];
                       
                        $i++;
                     }
                      


  
                     //burbuja
                     $longitud = count($matriz_xls);
                        for ($i = 0; $i < $longitud; $i++) {
                            for ($j = 0; $j < $longitud - 1; $j++) {
                                if ($matriz_xls[$j]['nickname'] > $matriz_xls[$j + 1]['nickname']) {
                                    $temporal = $matriz_xls[$j];
                                    $matriz_xls[$j] = $matriz_xls[$j + 1];
                                    $matriz_xls[$j + 1] = $temporal;
                                }
                            }
                        }
  
     /* echo "<pre>";
          print_r($matriz_com);
          //print_r($matriz);
          print_r($matriz_xls);          
          
       echo "</pre>";

    exit;*/

    if($request->query->get("vista")){ 
       return $this->render('gerencia/reporte_semana_saldo.html.twig', [             
             'gerencia' => $gerencia_logueada,
             'comision' => $matriz_com, 
             'totales' => $matriz_xls,
             'rango'=>$rango         
        ]);
    }

     
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('gerencia/reporte_semana_saldo.html.twig', [
                   
             'gerencia' => $gerencia_logueada,
             'comision' => $matriz_com, 
             'totales' => $matriz_xls,
             'rango'=>$rango    
        ]);
    

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'landscape' or 'portrait'
        $dompdf->setPaper('A4', 'portrait'); //landscape

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("reporte_semana_saldo.pdf", [
            "Attachment" => true
        ]);             

          echo 'lanffza';
        exit;
    }                                       

}
