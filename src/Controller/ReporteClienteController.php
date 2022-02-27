<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gerencia;
use App\Entity\Correccion;
use App\Entity\Carrera;
use App\Entity\Apuesta;
use App\Entity\AdjuntoPago;
use App\Entity\Cuenta;
use App\Entity\PagoPersonal;
use App\Entity\PagoPersonalSaldo;
use App\Entity\RetiroSaldo;
use App\Entity\Traspaso;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//pdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Include PhpSpreadsheet required namespaces
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/reporte/cliente")
 */
class ReporteClienteController extends AbstractController
{
    /**
     * @Route("/", name="reporte_cliente_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {           

      $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER');          
        
        /*$logged_user = $this->getUser();
        echo  $logged_user->getGerenciaPermiso()->getId().'--------//-----';
        exit;*/
        $user = $this->getUser();    

        // Render the twig view
        return $this->render('reporte_cliente/index.html.twig');

    }


   /**
     * @Route("/apuesta", name="reporte_cliente_apuesta", methods={"GET"})
     */
    public function apuesta(Request $request): Response
    {           

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 


        $repository = $this->getDoctrine()->getRepository(Apuesta::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c')
            ->innerJoin('a.apuestaDetalles','apd') 
            
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
            
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->andWhere('apd.perfil = :perfil')           
            ->setParameter('perfil', $user->getPerfil()->getId());
        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();    

        $spreadsheet = new Spreadsheet();


        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        
        //columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA CARRERA');
        $sheet->setCellValue('B'.$i, 'TIPO APUESTA');       
        $sheet->setCellValue('C'.$i, 'CARRERA'); 
        $sheet->setCellValue('D'.$i, 'HIPODROMO');
        $sheet->setCellValue('E'.$i, 'MONTO');
        
        $sheet->setCellValue('F'.$i, 'GANADOR');  
        $sheet->setCellValue('G'.$i, 'CODIGO CLIENTE GANADOR');
        $sheet->setCellValue('H'.$i, 'MONTO GANADOR');        
        $sheet->setCellValue('I'.$i, 'PERDEDOR');  
        $sheet->setCellValue('J'.$i, 'CODIGO CLIENTE PERDEDOR');
        $sheet->setCellValue('K'.$i, 'MONTO DEVUELTO POR CC');    

        $sheet->setCellValue('L'.$i, 'PAGADO POR');                        
        
        $i=3;
        foreach ($allRowsQuery as $row) {           
            $sheet->setCellValue('A'.$i, $row->getCarrera()->getFecha());      
            $sheet->setCellValue('B'.$i, $row->getTipo()->getNombre());           
            $sheet->setCellValue('C'.$i, $row->getCarrera()->getNumeroCarrera());
            $sheet->setCellValue('D'.$i, $row->getCarrera()->getHipodromo()->getNombre());           
            $sheet->setCellValue('E'.$i, $row->getMonto()); 
            
            if($row->getCuenta()){
                
                if($user->getPerfil() ==$row->getGanador()){

                    $sheet->setCellValue('F'.$i, $row->getGanador()->getUsuario()->getNombre());
                    $sheet->setCellValue('G'.$i, $row->getGanador()->getNickname());
                    $sheet->setCellValue('H'.$i, $row->getCuenta()->getSaldoGanador());
                }

                if($user->getPerfil() ==$row->getCuenta()->getPerdedor()){
                    $sheet->setCellValue('I'.$i, $row->getCuenta()->getPerdedor()->getUsuario()->getNombre());
                    $sheet->setCellValue('J'.$i, $row->getCuenta()->getPerdedor()->getNickname());
                    $sheet->setCellValue('K'.$i, $row->getCuenta()->getSaldoPerdedor()); 
                }
                
            }
            $sheet->setCellValue('L'.$i, $row->getCarrera()->getPagadoBy());        

            $i++;
        }    


        $SUMRANGE = 'H3:H'.$i;
        $sheet->setCellValue('A'.($i+2) , "TOTAL PAGADO A GANADOR");
        $sheet->setCellValue('B'.($i+2) , "=SUBTOTAL(109,$SUMRANGE)");  
        $sheet->setCellValue('C'.($i+2) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+2) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");  
        $SUMRANGE = 'K3:K'.$i;
        $sheet->setCellValue('A'.($i+3) , "MONTO DEVUELTO POR CC");
        $sheet->setCellValue('B'.($i+3) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('D'.($i+3) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)"); 


/*
        $SUMRANGE = 'E3:E'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL APUESTAS");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");
*/
        $SUMRANGE = 'H3:H'.$i;
        $SUMRANGE2 = 'K3:K'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL NETO");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE) + SUBTOTAL(109,$SUMRANGE2) ");             
        
        $sheet->setTitle("Apuestas");

        $sheet->setAutoFilter('A1:L'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'apuestas'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }




    /**
     * @Route("/retirosaldo", name="reporte_cliente_retiro_saldo", methods={"GET"})
     */
    public function retiroSaldo(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 
        
        $repository = $this->getDoctrine()->getRepository(RetiroSaldo::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')    
            
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')

            ->andWhere('a.perfil = :perfil')           
            ->setParameter('perfil', $user->getPerfil()->getId());
        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();    

        $spreadsheet = new Spreadsheet();


        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        
        //columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA');       
        $sheet->setCellValue('B'.$i, 'CODIGO CLIENTE');          
        $sheet->setCellValue('C'.$i, 'MONTO');
        $sheet->setCellValue('D'.$i, 'REFERENCIA');
        $sheet->setCellValue('E'.$i, 'PAGO POR');
        $sheet->setCellValue('F'.$i, 'VALIDADO'); 
        $sheet->setCellValue('G'.$i, 'VALIDADO POR');
        $sheet->setCellValue('H'.$i, 'CREADO POR');
        $sheet->setCellValue('I'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
                $sheet->setCellValue('B'.$i, $row->getPerfil()->getNickname());           
                $sheet->setCellValue('C'.$i, $row->getMonto());
                $sheet->setCellValue('D'.$i, $row->getNumeroReferencia());
               if( $row->getMetodoPago())
                    $sheet->setCellValue('F'.$i, $row->getMetodoPago()->getNombre() );
                
                if($row->getValidado())
                    $sheet->setCellValue('G'.$i, 'SI');
                else
                    $sheet->setCellValue('G'.$i, 'NO');
                $sheet->setCellValue('G'.$i, $row->getValidadoBy());
                $sheet->setCellValue('H'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('I'.$i, $row->getObservacion());             
                $i++;
            }
      
        
                    $bold = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];
                     
               

                    $sheet->getStyle('A1:O1')->applyFromArray($bold);
                     $sheet->getStyle('B'.($i+5))->applyFromArray($bold);
                     $sheet->getStyle('D'.($i+5))->applyFromArray($bold);

        $SUMRANGE = 'C3:C'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL SALDO ACUMULADO");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");          
        $sheet->setTitle("Retiros de saldo");

        $sheet->setAutoFilter('A1:I'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'retiro_de_saldo'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }
    /**
     * @Route("/abonosaldo", name="reporte_cliente_abono_saldo", methods={"GET"})
     */
    public function abonoSaldo(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
        $repository = $this->getDoctrine()->getRepository(AdjuntoPago::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

         if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')    
            
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')

            ->andWhere('a.perfil = :perfil')           
            ->setParameter('perfil', $user->getPerfil()->getId());
        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();    

        $spreadsheet = new Spreadsheet();


        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        
        //columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
         $sheet->getColumnDimension('J')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA'); 
        $sheet->setCellValue('B'.$i, 'TIPO');      
        $sheet->setCellValue('C'.$i, 'CODIGO CLIENTE');          
        $sheet->setCellValue('D'.$i, 'MONTO');
        $sheet->setCellValue('E'.$i, 'REFERENCIA');
        $sheet->setCellValue('F'.$i, 'PAGO POR');
        $sheet->setCellValue('G'.$i, 'VALIDADO'); 
        $sheet->setCellValue('H'.$i, 'VALIDADO POR');
        $sheet->setCellValue('I'.$i, 'CREADO POR');
        $sheet->setCellValue('J'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());   

                if($row->getPerfil()->getSaldoIlimitado()){
                   $sheet->setCellValue('B'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('B'.$i, 'pozo');
                }        
                
                $sheet->setCellValue('C'.$i, $row->getPerfil()->getNickname());           
                $sheet->setCellValue('D'.$i, $row->getMonto());
                $sheet->setCellValue('E'.$i, $row->getNumeroReferencia());
                
                if( $row->getMetodoPago())
                    $sheet->setCellValue('F'.$i, $row->getMetodoPago()->getNombre() );
                
                if($row->getValidado())
                    $sheet->setCellValue('G'.$i, 'SI');
                else
                    $sheet->setCellValue('G'.$i, 'NO');
                $sheet->setCellValue('H'.$i, $row->getValidadoBy());
                $sheet->setCellValue('I'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('J'.$i, $row->getObservacion());             
                $i++;
            }
      
        
                    $bold = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];
                     
               

                    $sheet->getStyle('A1:O1')->applyFromArray($bold);
                     $sheet->getStyle('B'.($i+5))->applyFromArray($bold);
                     $sheet->getStyle('D'.($i+5))->applyFromArray($bold);

        $SUMRANGE = 'D3:D'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");  

        $sheet->setTitle("Depositos por saldo");
        $sheet->setAutoFilter('A1:J'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'abono_de_saldo'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }


    /**
     * @Route("/traspaso", name="reporte_cliente_traspaso", methods={"GET"})
     */
    public function traspaso(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 
        
        $repository = $this->getDoctrine()->getRepository(Traspaso::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')        
            ->andWhere('a.abono = :perfil')
            ->orWhere('a.descuento = :perfil')                  
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')
            ->setParameter('perfil', $user->getPerfil()->getId());
        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();    

        $spreadsheet = new Spreadsheet();


        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        
        //columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA');          
        $sheet->setCellValue('B'.$i, 'MONTO');
        $sheet->setCellValue('C'.$i, 'USUARIO ABONO');
        $sheet->setCellValue('D'.$i, 'USUARIO DESCUENTO');
        $sheet->setCellValue('E'.$i, 'CREADO POR');
        $sheet->setCellValue('F'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());                 
                $sheet->setCellValue('B'.$i, $row->getMonto());
                $sheet->setCellValue('C'.$i, $row->getAbono()->getNickname());
                $sheet->setCellValue('D'.$i, $row->getDescuento()->getNickname());
                $sheet->setCellValue('E'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('F'.$i, $row->getObservacion());             
                $i++;
            }
      
        
                    $bold = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];
                     
               

                    $sheet->getStyle('A1:H1')->applyFromArray($bold);
                     $sheet->getStyle('B'.($i+5))->applyFromArray($bold);
                     $sheet->getStyle('D'.($i+5))->applyFromArray($bold);

        $SUMRANGE = 'B3:B'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL MONTO TRASPASO");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)"); 

        
        $sheet->setTitle("Traspasos");

        $sheet->setAutoFilter('A1:F'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'traspaso'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    } 

    /**
     * @Route("/depositosaldo", name="reporte_cliente_deposito_saldo", methods={"GET"})
     */
    public function depositoSaldo(Request $request): Response
    {          

        //AdjuntoPago
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE USER'); 

        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(PagoPersonalSaldo::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('a.perfil = :perfil')
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)            
            ->setParameter('perfil', $user->getPerfil()->getId());
        }

        // Find all the data, filter your query as you need
        $allRowsQuery = $allRowsQuery->getQuery()->getResult();    

        $spreadsheet = new Spreadsheet();
        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        
        //columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA');       
        $sheet->setCellValue('B'.$i, 'CODIGO CLIENTE');
        $sheet->setCellValue('C'.$i, 'CONCEPTO');        
        $sheet->setCellValue('D'.$i, 'MONTO'); 
        $sheet->setCellValue('E'.$i, 'CREADO POR');
        $sheet->setCellValue('F'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
                $sheet->setCellValue('B'.$i, $row->getPerfil()->getNickname());
                $sheet->setCellValue('C'.$i, $row->getConcepto());
                $sheet->setCellValue('D'.$i, $row->getMonto()); 
                $sheet->setCellValue('E'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('F'.$i, $row->getObservacion());             
                $i++;
            }
      
        
                    $bold = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];
                     
               

                    $sheet->getStyle('A1:O1')->applyFromArray($bold);
                     $sheet->getStyle('B'.($i+5))->applyFromArray($bold);
                     $sheet->getStyle('D'.($i+5))->applyFromArray($bold);

        $SUMRANGE = 'D3:D'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");          
        $sheet->setTitle("Depositos por saldo");

        $sheet->setAutoFilter('A1:F'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'deposito_por_saldo'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }    


}
/*
"=SUBTOTAL(109,$SUMRANGE)"
Las celdas ocultas no se tienen en cuenta en el cálculo:

    101: PROMEDIO
    102: CONTAR
    103: CONTARA
    104: MAX
    105: MIN
    106: PRODUCTO
    107: DESVEST
    108: DESVESTP
    109: SUMA
    110: VAR
    111: VARP*/
/*
    $sheet->getStyle('A1')->applyFromArray(
    array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'FF0000')
        )
    )
);*/