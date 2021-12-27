<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\Gerencia;
use App\Entity\Apuesta;
use App\Entity\ApuestaDetalle;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//pdf
use Dompdf\Dompdf;
use Dompdf\Options;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMINISTRATIVO")
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator,  Request $request): Response
    {           

      $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO');          
        
        /*$logged_user = $this->getUser();
        echo  $logged_user->getGerenciaPermiso()->getId().'--------//-----';
        exit;*/

        $user = $this->getUser();

        // Render the twig view
        return $this->render('user/index.html.twig', [
            'perfils' => $user->getPerfil()->getGerencia()->getPerfils(),          
           
        ]);


    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,  UserPasswordEncoderInterface $passwordEncoder): Response
    {        

        $user = new User();
        $user_logueado = $this->getUser();
        //echo $user_logueado->getPerfil()->getGerencia();
        //exit;        

         /*$repository = $this->getDoctrine()->getRepository(Gerencia::class);
         $gerencia = $repository->find($user_logueado->getPerfil()->getGerencia()->getId());*/       
       
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);       


        if ($form->isSubmitted() && $form->isValid()) { 

          //echo $user->getPerfil()->getNickname();
          //exit;   
             $user->getPerfil()->setGerencia($user_logueado->getPerfil()->getGerencia());
             $user->getPerfil()->setUsuario($user);

          /*$repository = $this->getDoctrine()->getRepository(User::class);
          $usuario = $repository->findOneByEmail($user->getEmail());

          if($usuario){

            $usuario
          }*/       

            $entityManager = $this->getDoctrine()->getManager();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);    

            $entityManager->persist($user);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(User $user): Response
    {

        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $user->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            return $this->redirectToRoute('user_index');
        } 
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
      
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $user->getPerfil()->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            return $this->redirectToRoute('user_index');
        } 

        $userOld['password'] = $user->getPassword();   

        $form = $this->createForm(UserType::class, $user);
    
         $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
  
          
          //set user password old

            if(!$user->getPassword()){
                $user->setPassword($userOld['password']);
            }else{
               $password = $passwordEncoder->encodePassword($user, $user->getPassword());
               $user->setPassword($password);
            }

               
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,                                 
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        /*if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Los cambios fueron realizados!'
            );
        }*/

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/reporte_dia", name="user_reporte_dia", methods={"GET"})
     */
    public function reporte_dia(Request $request): Response
    {           
 
 
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 
        
        if($request->get("perfil"))
          $user = $this->getDoctrine()->getRepository(Perfil::class)->find($request->get("perfil"));
        else       
          $user = $this->getUser()->getPerfil();   
        
        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $user->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            return $this->redirectToRoute('user_index');

        }

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
            $perfil = $user->getId(); 
                        
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c')
            ->innerJoin('a.apuestaDetalles','apd') 
            
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
            
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->andWhere('apd.perfil = :perfil')           
            ->setParameter('perfil', $perfil);

            $week_number = date('W', strtotime($fecha2));
            $year = date('Y', strtotime($fecha2));
            /*for($day=1; $day<=7; $day++)
            {
                echo date('m/d/Y', strtotime($year."W".$week_number.$day))."\n";
            }*/

            $week_first =  date('Y-m-d', strtotime($year."W".$week_number.(1) ));
            $week_last =  date('Y-m-d', strtotime($year."W".$week_number.(7) ));

             $queryTotalSemana  = $queryTotalSemana
              ->innerJoin('a.carrera','c')
              ->innerJoin('a.apuestaDetalles','apd') 
              
              ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
              
              ->orderBy('c.fecha', 'ASC')
              ->setParameter('fecha1',  $week_first)
              ->setParameter('fecha2',  $week_last)
              ->andWhere('apd.perfil = :perfil')           
              ->setParameter('perfil', $perfil);

        }


        // Find all the data, filter your query as you need
        $queryTotalSemana = $queryTotalSemana->getQuery()->getResult();
  
        $total_semana = 0;
        foreach ($queryTotalSemana as $row){

               if($perfil == $row->getCuenta()->getGanador()->getId()){

                  $monto = $row->getCuenta()->getSaldoGanador();
                  $total_semana  += $monto;

                }else{

                   $monto = ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);
                   $total_semana +=  $monto;
                } 

               // echo $monto.'<br>';    

        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();
        $matriz = array();
        foreach ($allRowsQuery as $row){
          
            $fila[$row->getCarrera()->getNumeroCarrera()] =  $row->getCarrera()->getNumeroCarrera();

            $columna[$row->getCarrera()->getHipodromo()->getId()]['nombre'] =  $row->getCarrera()->getHipodromo()->getNombre();

            $matriz[$row->getCarrera()->getNumeroCarrera()]['numero'] = $row->getCarrera()->getNumeroCarrera();            

             $columna[$row->getCarrera()->getHipodromo()->getId()]['id'] =  $row->getCarrera()->getHipodromo()->getId();
             $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['hipodromo'] = $row->getCarrera()->getHipodromo()->getId() ;

             $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['coordenada'] = $row->getCarrera()->getNumeroCarrera().'_'.$row->getCarrera()->getHipodromo()->getId() ;

            if(!isset($matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['total_carrera'])){
              
                //si es ganador o perdedor
                if($perfil == $row->getCuenta()->getGanador()->getId()){
                  $monto = $row->getCuenta()->getSaldoGanador();
                  $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['total_carrera'] =  $monto;

                  //$matriz[$row->getCarrera()->getNumeroCarrera()]['total_hipodromo'] = $monto;

                }else{
                   $monto =  ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);

                   $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['total_carrera'] = $monto;

                   //$matriz[$row->getCarrera()->getNumeroCarrera()]['total_hipodromo'] = $monto;
                }
            }else{
               if($perfil == $row->getCuenta()->getGanador()->getId()){

                  $monto = $row->getCuenta()->getSaldoGanador();
                  $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['total_carrera'] += $monto;
                  //$matriz[$row->getCarrera()->getNumeroCarrera()]['total_hipodromo'] += $monto;

                }else{

                   $monto = ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);
                   $matriz[$row->getCarrera()->getNumeroCarrera()]['carreras'][$row->getCarrera()->getHipodromo()->getId()]['total_carrera'] +=  $monto;

                   //$matriz[$row->getCarrera()->getNumeroCarrera()]['total_hipodromo'] += $monto;
                }
            }

        }

      if(!isset($columna)){
         $this->addFlash(
             'danger',
             'No hay registros'
            );
            
            return $this->redirectToRoute('user_index');
            
      }
           
      foreach ($columna as $row) {

          $columna[$row['id']]['total_hipodromo'] = 0;    
       
        foreach($matriz as $fila){
            $tmp_hipodromo = array();
            foreach($fila['carreras'] as $carrera){  
                $tmp_hipodromo[] = $carrera['hipodromo'];
            }
            if(!in_array($row['id'], $tmp_hipodromo)  ){
              $matriz[$fila['numero']]['carreras'][$row['id']]['total_carrera'] = 0;
              $matriz[$fila['numero']]['carreras'][$row['id']]['hipodromo'] = $row['id'];
            }else{
                $columna[$row['id']]['total_hipodromo'] += $matriz[$fila['numero']]['carreras'][$row['id']]['total_carrera'];
            }
        }  
      }

//burbuja carreras en matriz

        foreach($matriz as $key=> $fila){
            $tmp_rows = array();
            foreach($fila['carreras'] as $carrera){  
                $tmp_rows[] = $carrera;
            }
            $longitud = count($tmp_rows);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($tmp_rows[$j]["hipodromo"] > $tmp_rows[$j + 1]["hipodromo"]) {
                        $temporal = $tmp_rows[$j];
                        $tmp_rows[$j] = $tmp_rows[$j + 1];
                        $tmp_rows[$j + 1] = $temporal;
                    }
                }
            } 
            $matriz[$key]['carreras']=$tmp_rows;      

        }       

         //burbuja fila 
        $filas = array();
        foreach ($matriz as $row) {
          $filas[] = $row; 
        }
         $longitud = count($filas);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($filas[$j]["numero"] > $filas[$j + 1]["numero"]) {
                        $temporal = $filas[$j];
                        $filas[$j] = $filas[$j + 1];
                        $filas[$j + 1] = $temporal;
                    }
                }
            }

         //burbuja columna
     
        $columnas = array();
        foreach ($columna as $row) {
          $columnas[] = $row; 
        }
         $longitud = count($columna);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($columnas[$j]["id"] > $columnas[$j + 1]["id"]) {
                        $temporal = $columnas[$j];
                        $columnas[$j] = $columnas[$j + 1];
                        $columnas[$j + 1] = $temporal;
                    }
                }
            }            

     



    //echo  '-/-'.$fecha2.'---'.date('W', strtotime($fecha2));

  /*     echo $total_semana;
       echo "<pre>";
          print_r($filas);
          print_r($columnas);
        echo "</pre>";

        exit;
*/

 if($request->query->get("vista")){ 
         return $this->render('user/pdf.html.twig', [
             'filas' => $filas,
             'columnas' => $columnas,
             'user' => $user,
             'rango'=>$rango,
             'total_semana' => $total_semana
          
        ]);
  }    
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('user/pdf.html.twig', [
             'filas' => $filas,
             'columnas' => $columnas,
             'user' => $user,
             'rango'=>$rango,
             'total_semana' => $total_semana
        ]);
        

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'landscape'); //portrait

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("rpt1.pdf", [
            "Attachment" => true
        ]);             

          echo 'lanffza';
        exit;
    } 


    /**
     * @Route("/reporte_jugada", name="user_reporte_jugada", methods={"GET"})
     */
    public function reporte_jugada(Request $request): Response
    {           
 
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 
        
        if($request->get("perfil"))
          $user = $this->getDoctrine()->getRepository(Perfil::class)->find($request->get("perfil"));
        else       
          $user = $this->getUser()->getPerfil();

        $gerencia_logueada = $this->getUser()->getPerfil()->getGerencia()->getId();
        $gerencia = $user->getGerencia()->getId();

        if($gerencia_logueada != $gerencia){            
            $this->addFlash(
             'danger',
             'Acceso no autorizado'
            );
            return $this->redirectToRoute('user_index');

        }

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
            $perfil = $user->getId(); 
                        
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c')
            ->innerJoin('a.apuestaDetalles','apd') 
            
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
            
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->andWhere('apd.perfil = :perfil')           
            ->setParameter('perfil', $perfil);


        }


        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();
        $matriz = null;
        $matriz_total = array();
        foreach ($allRowsQuery as $row){          
  
             $apuesta_detalle = $this->getDoctrine()->getRepository(ApuestaDetalle::class)->findByApuestaCaballoNotNull($row->getId());
             $caballos = $apuesta_detalle->getCaballos();

           $str_cab = null;
            foreach ($caballos as $value) {
                $str_cab .= $value.' ';
            }

          $fec =  $row->getCarrera()->getFecha()->format('Y-m-d');
          $hip =  $row->getCarrera()->getHipodromo()->getId();
          $num =  $row->getCarrera()->getNumeroCarrera();
          $tip = $row->getTipo()->getNombre();


          //exit; 

             if($apuesta_detalle->getPerfil()->getId() == $perfil){
                $matriz[$fec][$hip][$num][$tip][$str_cab]['jugada'] = 'JUGO';
             }else{
                $matriz[$fec][$hip][$num][$tip][$str_cab]['jugada'] = 'PAGO';
             }
         

            $matriz[$fec][$hip][$num][$tip][$str_cab]['fecha'] = $fec;

            $matriz[$fec][$hip][$num][$tip][$str_cab]['hipodromo'] = $row->getCarrera()->getHipodromo()->getNombre();
            $matriz[$fec][$hip][$num][$tip][$str_cab]['hipodromo_id'] = $row->getCarrera()->getHipodromo()->getId();

            $matriz[$fec][$hip][$num][$tip][$str_cab]['numero_carrera'] = $row->getCarrera()->getNumeroCarrera();       

            $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta'] = $row->getTipo()->getNombre(); 

            
            $matriz[$fec][$hip][$num][$tip][$str_cab]['caballo'] = $str_cab; 

           

            if(!isset($matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_monto'])){
              
                //si es ganador o perdedor
                if($perfil == $row->getCuenta()->getGanador()->getId()){
                  
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_monto'] = $row->getMonto();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_gana'] = $row->getCuenta()->getSaldoGanador();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_pierde'] = 0; 


                }else{           

                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_monto'] = $row->getMonto();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_gana'] = 0;
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_pierde'] = ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);

              
                }
            }else{
                         //si es ganador o perdedor
                if($perfil == $row->getCuenta()->getGanador()->getId()){
                  
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_monto'] += $row->getMonto();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_gana'] += $row->getCuenta()->getSaldoGanador();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_pierde'] += 0;              

                }else{           

                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_monto'] += $row->getMonto();
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_gana'] += 0;
                  $matriz[$fec][$hip][$num][$tip][$str_cab]['apuesta_pierde'] += ($row->getMonto() - $row->getCuenta()->getSaldoPerdedor()) * (-1);                
                   
                }
            }



        }


      if(!isset($matriz)){
         $this->addFlash(
             'danger',
             'No hay registros'
            );
            
            return $this->redirectToRoute('user_index');
            
      }     

      $mat = null;
      foreach ($matriz as $valuea) {
         foreach ($valuea as $valueb) {
            foreach ($valueb as $valuec) {
              foreach ($valuec as $valued) {
                foreach ($valued as $valuee) {
               
                    /*echo "<pre>";
                        print_r($valuee);
                     echo "</pre>";*/

                    $mat[$valuee['fecha']][] = $valuee;
               }

              }
            }
           
         }
      }
      
      foreach ($mat as $elemento) {
          foreach ($elemento as $row) {
         
                $fec = $row['fecha'];
                $hip = $row['hipodromo_id'];

                if(!isset($matriz_total[$fec][$hip])){
                    $matriz_total[$fec][$hip]['apuesta_gana'] = $row['apuesta_gana'];
                    $matriz_total[$fec][$hip]['apuesta_pierde'] = $row['apuesta_pierde'];
                    $matriz_total[$fec][$hip]['hipodromo'] = $row['hipodromo'];

                }else{
                    $matriz_total[$fec][$hip]['apuesta_gana'] += $row['apuesta_gana'];
                    $matriz_total[$fec][$hip]['apuesta_pierde'] += $row['apuesta_pierde'];
                }
            }  
      }
  
/*
       echo "<pre>";
          //print_r($mat);
          //print_r($matriz);
          print_r($matriz_total);
        echo "</pre>";

        exit;
*/
if($request->query->get("vista")){ 
        return $this->render('user/reporte_jugada.html.twig', [
            'filas' => $mat,          
             'user' => $user,
             'totales' => $matriz_total,
             'rango'=>$rango
            
          
        ]);
    }  
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('user/reporte_jugada.html.twig', [
             'filas' => $mat,          
             'user' => $user,
             'totales' => $matriz_total,
             'rango'=>$rango
        ]);
    

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'landscape'); //portrait

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("rpt1.pdf", [
            "Attachment" => true
        ]);             

          echo 'lanffza';
        exit;
    }                   


}
