<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gerencia;
use App\Entity\Correccion;
use App\Entity\Carrera;
use App\Entity\Apuesta;
use App\Entity\AdjuntoPago;
use App\Entity\Cuenta;
use App\Entity\Perfil;
use App\Entity\Traspaso;
use App\Entity\PagoCliente;
use App\Entity\PagoPersonal;
use App\Entity\PagoPersonalSaldo;
use App\Entity\RetiroSaldo;
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
 * @Route("/reporte")
 */
class ReporteController extends AbstractController
{
    /**
     * @Route("/", name="reporte_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {           

      $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO');          
        
        /*$logged_user = $this->getUser();
        echo  $logged_user->getGerenciaPermiso()->getId().'--------//-----';
        exit;*/

        $user = $this->getUser();
    

        // Render the twig view
        return $this->render('reporte/index.html.twig');

    }

    /**
     * @Route("/correccion", name="reporte_correccion", methods={"GET"})
     */
    public function correccion(Request $request): Response
    {           

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(Correccion::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
            ->andWhere('c.gerencia = :gerencia')
            ->innerJoin('a.apuesta','ap')
            ->innerJoin('ap.carrera', 'c')
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA CORRECCION');       
        $sheet->setCellValue('B'.$i, 'CARRERA');
        $sheet->setCellValue('C'.$i, 'HIPODROMO');        
        $sheet->setCellValue('D'.$i, 'MONTO DE LA APUESTA'); 
        $sheet->setCellValue('E'.$i, 'CREADO POR');
        $sheet->setCellValue('F'.$i, 'OBSERVACION');
        $sheet->setCellValue('G'.$i, 'OBSERVACION SISTEMA');
        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
                $sheet->setCellValue('B'.$i, $row->getApuesta()->getCarrera()->getNumeroCarrera());
                $sheet->setCellValue('C'.$i, $row->getApuesta()->getCarrera()->getHipodromo()->getNombre());
                $sheet->setCellValue('D'.$i, $row->getApuesta()->getMonto());
                $sheet->setCellValue('E'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('F'.$i, $row->getObservacion());
                $sheet->setCellValue('G'.$i, $row->getObservacionSistema());
                $i++;
            }
      
        
        $sheet->setTitle("Correcciones en apuestas");

        $sheet->setAutoFilter('A1:G'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'correciones'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }

    /**
     * @Route("/carreras", name="reporte_carreras", methods={"GET"})
     */
    public function carreras(Request $request): Response
    {           

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 


        $repository = $this->getDoctrine()->getRepository(Carrera::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.fecha BETWEEN :fecha1 AND :fecha2')
            ->andWhere('a.gerencia = :gerencia')
            ->orderBy('a.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->setCellValue('A'.$i, 'FECHA CARRERA');       
        $sheet->setCellValue('B'.$i, 'NUMERO'); 
        $sheet->setCellValue('C'.$i, 'HIPODROMO');
        $sheet->setCellValue('D'.$i, 'APUESTAS');  
        $sheet->setCellValue('E'.$i, 'TOTAL PAGADO');
        $sheet->setCellValue('F'.$i, 'TOTAL GANANCIA');
        $sheet->setCellValue('G'.$i, 'ORDEN OFICIAL');
        $sheet->setCellValue('H'.$i, 'STATUS');  
        $sheet->setCellValue('I'.$i, 'PAGADO POR');
        $sheet->setCellValue('J'.$i, 'CREADO POR');                          
        
        $i=3;
        foreach ($allRowsQuery as $row) {           
            $sheet->setCellValue('A'.$i, $row->getFecha());           
            $sheet->setCellValue('B'.$i, $row->getNumeroCarrera());
            $sheet->setCellValue('C'.$i, $row->getHipodromo()->getNombre()); 
            $sheet->setCellValue('D'.$i, count($row->getApuestas()));
            $sheet->setCellValue('E'.$i, $row->getTotalPagado());
            $sheet->setCellValue('F'.$i, $row->getTotalGanancia());
            
            $orden = implode('-', $row->getOrdenOficial());            

            $sheet->setCellValue('G'.$i, $orden);            
            $sheet->setCellValue('H'.$i, $row->getStatus());
            $sheet->setCellValue('I'.$i, $row->getPagadoBy());
            $sheet->setCellValue('J'.$i, $row->getCreatedBy());           

            $i++;
        }        
        
        $sheet->setTitle("Carreras");

        $sheet->setAutoFilter('A1:J'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'carreras_totales'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }    


    /**
     * @Route("/deposito", name="reporte_deposito", methods={"GET"})
     */
    public function deposito(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(PagoPersonal::class);

        if ($request->query->get("pago_cliente")) {
            $repository = $this->getDoctrine()->getRepository(PagoCliente::class); 
        }            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->setCellValue('B'.$i, 'TIPO');
        $sheet->setCellValue('C'.$i, 'CODIGO CLIENTE');
        $sheet->setCellValue('D'.$i, 'CONCEPTO');        
        $sheet->setCellValue('E'.$i, 'MONTO'); 
        $sheet->setCellValue('F'.$i, 'METODO DE PAGO');
        $sheet->setCellValue('G'.$i, 'REFERENCIA');
        $sheet->setCellValue('H'.$i, 'CREADO POR');
        $sheet->setCellValue('I'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
                
                if($row->getPerfil()->getSaldoIlimitado()){
                   $sheet->setCellValue('B'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('B'.$i, 'pozo');
                }

                $sheet->setCellValue('C'.$i, $row->getPerfil()->getNickname());
                $sheet->setCellValue('D'.$i, $row->getConcepto());
                $sheet->setCellValue('E'.$i, $row->getMonto());
                $sheet->setCellValue('F'.$i, $row->getMetodoPago()->getNombre());
                $sheet->setCellValue('G'.$i, $row->getNumeroReferencia()); 
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

        $SUMRANGE = 'E3:E'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");        
        
        $sheet->setTitle("Depositos directos");

        $sheet->setAutoFilter('A1:I'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'deposito'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }


    /**
     * @Route("/depositosaldo", name="reporte_deposito_saldo", methods={"GET"})
     */
    public function depositoSaldo(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 

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
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->setCellValue('B'.$i, 'TIPO');
        $sheet->setCellValue('C'.$i, 'CODIGO CLIENTE');
        $sheet->setCellValue('D'.$i, 'CONCEPTO');        
        $sheet->setCellValue('E'.$i, 'MONTO'); 
        $sheet->setCellValue('F'.$i, 'CREADO POR');
        $sheet->setCellValue('G'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());
                
                if($row->getPerfil()->getSaldoIlimitado()){
                   $sheet->setCellValue('B'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('B'.$i, 'pozo');
                }

                $sheet->setCellValue('C'.$i, $row->getPerfil()->getNickname());
                $sheet->setCellValue('D'.$i, $row->getConcepto());
                $sheet->setCellValue('E'.$i, $row->getMonto()); 
                $sheet->setCellValue('F'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('G'.$i, $row->getObservacion());             
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

        $SUMRANGE = 'E3:E'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");          
        $sheet->setTitle("Depositos por saldo");

        $sheet->setAutoFilter('A1:G'.$i);
        
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


    /**
     * @Route("/retirosaldo", name="reporte_retiro_saldo", methods={"GET"})
     */
    public function retiroSaldo(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
        $repository = $this->getDoctrine()->getRepository(RetiroSaldo::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->setTitle("Retiros de saldo");

        $sheet->setAutoFilter('A1:J'.$i);
        
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
     * @Route("/abonosaldo", name="reporte_abono_saldo", methods={"GET"})
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
            ->andWhere('p.gerencia = :gerencia')
            ->innerJoin('a.perfil','p')         
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
     * @Route("/usuarios", name="reporte_usuarios", methods={"GET"})
     */
    public function clientes(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
        $repository = $this->getDoctrine()->getRepository(Perfil::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            //->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('a.gerencia = :gerencia')
                 
            ->orderBy('a.nickname', 'ASC')
            //->setParameter('fecha1', $fecha1. ' 00:00:00')
            //->setParameter('fecha2', $fecha2. ' 11:59:59')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
      
            
        $sheet->setCellValue('A'.$i, 'CODIGO CLIENTE'); 
        $sheet->setCellValue('B'.$i, 'SALDO'); 
        $sheet->setCellValue('C'.$i, 'TIPO');
        $sheet->setCellValue('D'.$i, 'ACTIVO');               
        $sheet->setCellValue('E'.$i, 'SUELDO');
        $sheet->setCellValue('F'.$i, 'PORCENTAJE GANAR');
        $sheet->setCellValue('G'.$i, 'PORCENTAJE PERDER');


        $sheet->setCellValue('H'.$i, 'ROL');
        $sheet->setCellValue('I'.$i, 'NOMBRE COMPLETO');
        $sheet->setCellValue('J'.$i, 'EMAIL');
        $sheet->setCellValue('K'.$i, 'TELEFONO');
        $sheet->setCellValue('L'.$i, 'CREADO POR');
        $sheet->setCellValue('M'.$i, 'FECHA CREACION');
        $sheet->setCellValue('N'.$i, 'ACTUALIZADO POR');
        $sheet->setCellValue('O'.$i, 'FECHA ACTUALIZACION');      

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getNickname());  
                $sheet->setCellValue('B'.$i, $row->getSaldo());


                if($row->getSaldoIlimitado()){
                   $sheet->setCellValue('C'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('C'.$i, 'pozo');
                }


                if($row->getActivo())
                    $sheet->setCellValue('D'.$i, 'SI');
                else
                    $sheet->setCellValue('D'.$i, 'NO');

                 $sheet->setCellValue('E'.$i, $row->getSueldo()); 

                $sheet->setCellValue('F'.$i, $row->getPorcentajeGanar()); 
                $sheet->setCellValue('G'.$i, $row->getPorcentajePerder());                
                         
                $str_rol=NULL;
                foreach ($row->getRoles() as $val) {
                    $str_rol .= $val.' ';
                }

                $sheet->setCellValue('H'.$i, $str_rol);
                $sheet->setCellValue('I'.$i, $row->getUsuario()->getNombre().' '.$row->getUsuario()->getApellido());
                $sheet->setCellValue('J'.$i, $row->getUsuario()->getEmail());  
                $sheet->setCellValue('K'.$i, $row->getUsuario()->getTelefono());              
                $sheet->setCellValue('L'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('M'.$i, $row->getCreatedAt());
                $sheet->setCellValue('N'.$i, $row->getUpdatedBy());
                $sheet->setCellValue('O'.$i, $row->getUpdatedAt()); 
                       
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

        $SUMRANGE = 'B3:B'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('C'.($i+5) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");  
        $sheet->setTitle("Clientes");

        $sheet->setAutoFilter('A1:O'.$i);
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = 'usuarios'.$fecha1.'-'.$fecha2.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);       
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);        

    }



    /**
     * @Route("/traspaso", name="reporte_traspaso", methods={"GET"})
     */
    public function traspaso(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 
        
        $repository = $this->getDoctrine()->getRepository(Traspaso::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('a.gerencia = :gerencia')                
            ->orderBy('a.createdAt', 'ASC')
            ->setParameter('fecha1', $fecha1. ' 00:00:00')
            ->setParameter('fecha2', $fecha2. ' 11:59:59')
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->setCellValue('C'.$i, 'TIPO');
        $sheet->setCellValue('D'.$i, 'USUARIO ABONO');
        $sheet->setCellValue('E'.$i, 'TIPO');
        $sheet->setCellValue('F'.$i, 'USUARIO DESCUENTO');
        $sheet->setCellValue('G'.$i, 'CREADO POR');
        $sheet->setCellValue('H'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());                 
                $sheet->setCellValue('B'.$i, $row->getMonto());
                if($row->getAbono()->getSaldoIlimitado()){
                   $sheet->setCellValue('C'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('C'.$i, 'pozo');
                }   
                $sheet->setCellValue('D'.$i, $row->getAbono()->getNickname());

                if($row->getDescuento()->getSaldoIlimitado()){
                   $sheet->setCellValue('E'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('E'.$i, 'pozo');
                } 
                $sheet->setCellValue('F'.$i, $row->getDescuento()->getNickname());
                $sheet->setCellValue('G'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('H'.$i, $row->getObservacion());             
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

        $sheet->setAutoFilter('A1:H'.$i);
        
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
     * @Route("/apuesta", name="reporte_apuesta", methods={"GET"})
     */
    public function apuesta(Request $request): Response
    {           

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 


        $repository = $this->getDoctrine()->getRepository(Apuesta::class);            
        
        $user = $this->getUser();

        $allRowsQuery = $repository->createQueryBuilder('a'); 

        //example filter code, you must uncomment and modify

        if ($request->query->get("fecha1")) {
            $fecha1 = $request->query->get("fecha1"); 
            $fecha2 = $request->query->get("fecha2"); 
                        
            $allRowsQuery = $allRowsQuery
            ->innerJoin('a.carrera','c') 
            //->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
            ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
            ->andWhere('c.gerencia = :gerencia')
            ->orderBy('c.fecha', 'ASC')
            ->setParameter('fecha1', $fecha1)
            ->setParameter('fecha2', $fecha2)
            ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());
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
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA CARRERA');
        $sheet->setCellValue('B'.$i, 'FECHA APUESTA');
        $sheet->setCellValue('C'.$i, 'TIPO APUESTA');       
        $sheet->setCellValue('D'.$i, 'CARRERA'); 
        $sheet->setCellValue('E'.$i, 'HIPODROMO');
        $sheet->setCellValue('F'.$i, 'MONTO');
        
        $sheet->setCellValue('G'.$i, 'GANADOR');
        $sheet->setCellValue('H'.$i, 'TIPO');          
        $sheet->setCellValue('I'.$i, 'CODIGO CLIENTE GANADOR');
        $sheet->setCellValue('J'.$i, 'MONTO GANADOR');
        $sheet->setCellValue('K'.$i, 'PORCENTAJE GANADOR');          
        $sheet->setCellValue('L'.$i, 'PERDEDOR');  
        $sheet->setCellValue('M'.$i, 'TIPO');
        $sheet->setCellValue('N'.$i, 'CODIGO CLIENTE PERDEDOR');
        $sheet->setCellValue('O'.$i, 'MONTO DEVUELTO POR CC');
        $sheet->setCellValue('P'.$i, 'PORCENTAJE PERDEDOR');       
        $sheet->setCellValue('Q'.$i, 'GANANCIA CASA');
        $sheet->setCellValue('R'.$i, 'PAGADO POR');
        $sheet->setCellValue('S'.$i, 'CREADO POR');                          
        
        $i=3;
        foreach ($allRowsQuery as $row) {           
            $sheet->setCellValue('A'.$i, $row->getCarrera()->getFecha());
            $sheet->setCellValue('B'.$i, $row->getCreatedAt()); 
            $sheet->setCellValue('C'.$i, $row->getTipo()->getNombre());           
            $sheet->setCellValue('D'.$i, $row->getCarrera()->getNumeroCarrera());
            $sheet->setCellValue('E'.$i, $row->getCarrera()->getHipodromo()->getNombre());           
            $sheet->setCellValue('F'.$i, $row->getMonto()); 
            
            if($row->getCuenta()){
                $sheet->setCellValue('G'.$i, $row->getGanador()->getUsuario()->getNombre());

                if($row->getGanador()->getSaldoIlimitado()){
                   $sheet->setCellValue('H'.$i, 'avalado');     
                }else{
                   $sheet->setCellValue('H'.$i, 'pozo');
                }

                $sheet->setCellValue('I'.$i, $row->getGanador()->getNickname());
                $sheet->setCellValue('J'.$i, $row->getCuenta()->getSaldoGanador());
                $sheet->setCellValue('K'.$i, $row->getCuenta()->getGanador()->getPorcentajeGanar());

                //if($row->getCuenta()->getPerdedor()){
                    $sheet->setCellValue('L'.$i, $row->getCuenta()->getPerdedor()->getUsuario()->getNombre());

                    if($row->getCuenta()->getPerdedor()->getSaldoIlimitado()){
                       $sheet->setCellValue('M'.$i, 'avalado');     
                    }else{
                       $sheet->setCellValue('M'.$i, 'pozo');
                    }
                    $sheet->setCellValue('N'.$i, $row->getCuenta()->getPerdedor()->getNickname()); 
                //}
               
                $sheet->setCellValue('O'.$i, $row->getCuenta()->getSaldoPerdedor()); 
                $sheet->setCellValue('P'.$i,$row->getCuenta()->getPerdedor()->getPorcentajePerder());
                $sheet->setCellValue('Q'.$i, $row->getCuenta()->getSaldoCasa()); 
            }

            $sheet->setCellValue('R'.$i, $row->getCarrera()->getPagadoBy());
            $sheet->setCellValue('S'.$i, $row->getCarrera()->getCreatedBy());           

            $i++;
        }    


        $SUMRANGE = 'J3:H'.$i;
        $sheet->setCellValue('A'.($i+2) , "TOTAL PAGADO A GANADOR");
        $sheet->setCellValue('B'.($i+2) , "=SUBTOTAL(109,$SUMRANGE)");  
        $sheet->setCellValue('C'.($i+2) , "PROMEDIO");
        $sheet->setCellValue('D'.($i+2) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");  
        $SUMRANGE = 'O3:O'.$i;
        $sheet->setCellValue('A'.($i+3) , "MONTO DEVUELTO POR CC");
        $sheet->setCellValue('B'.($i+3) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('D'.($i+3) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)"); 
        $SUMRANGE = 'Q3:Q'.$i;
        $sheet->setCellValue('A'.($i+4) , "GANANCIA OFICIAL");
        $sheet->setCellValue('B'.($i+4) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('D'.($i+4) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)"); 
        $SUMRANGE = 'F3:F'.$i;
        $sheet->setCellValue('A'.($i+5) , "TOTAL DINERO JUGADO");
        $sheet->setCellValue('B'.($i+5) , "=SUBTOTAL(109,$SUMRANGE)");
        $sheet->setCellValue('D'.($i+5) , "=ROUND(SUBTOTAL(101,$SUMRANGE),2)");             
        
        $sheet->setTitle("Apuestas");

        $sheet->setAutoFilter('A1:S'.$i);
        
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
     * @Route("/total_cliente", name="reporte_total_cliente", methods={"GET"})
     */
    public function total_cliente(Request $request): Response
    {    

        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATIVO', null, 'User tried to access a page without having ROLE ADMINISTRATIVO'); 


        $repository = $this->getDoctrine()->getRepository(Cuenta::class);            
        
        $user = $this->getUser();       

        //example filter code, you must uncomment and modify
        $spreadsheet = new Spreadsheet();

        $sheet_index = 0;
        for($h=0;$h<=9;$h++){


                     $allRowsQuery = $repository->createQueryBuilder('a'); 
                    if ($request->query->get("fecha1")) {
                        $fecha1 = $request->query->get("fecha1"); 
                        $fecha2 = $request->query->get("fecha2"); 
                                    
                        $allRowsQuery = $allRowsQuery
                        ->innerJoin('a.apuesta','ap')
                        ->innerJoin('ap.carrera','c') 
                        //->andWhere('a.createdAt BETWEEN :fecha1 AND :fecha2')
                        ->andWhere('c.fecha BETWEEN :fecha1 AND :fecha2')
                        ->andWhere('c.gerencia = :gerencia')
                        ->orderBy('c.fecha', 'ASC')
                        ->setParameter('fecha1', $fecha1)
                        ->setParameter('fecha2', $fecha2)
                        ->setParameter('gerencia', $user->getPerfil()->getGerencia()->getId());

                        if($h>0){
                          $allRowsQuery = $allRowsQuery
                            ->setParameter('hipodromo', $h)
                            ->andWhere('c.hipodromo = :hipodromo');  
                        }
                       
                    }

                    // Find all the data, filter your query as you need
                    $allRowsQuery = $allRowsQuery->getQuery()->getResult();  
                      $total_casa =0;
                      $matriz=NULL;
                      $matriz=array();
                      foreach ($allRowsQuery as $row) {
                         // echo $row->getId().'-';

                          if(isset($matriz[$row->getGanador()->getId()]['total_ganador'])){
                            $matriz[$row->getGanador()->getId()]['total_ganador'] += $row->getSaldoGanador();
                          }else{
                            $matriz[$row->getGanador()->getId()]['total_ganador'] = $row->getSaldoGanador();
                            $matriz[$row->getGanador()->getId()]['nombre'] = $row->getGanador()->getNickname();

                            $matriz[$row->getGanador()->getId()]['saldo_ilimitado'] = $row->getGanador()->getSaldoIlimitado();
                          }

                          if(isset($matriz[$row->getPerdedor()->getId()]['total_perdedor'])){
                            $matriz[$row->getPerdedor()->getId()]['total_perdedor'] += $row->getApuesta()->getMonto() - $row->getSaldoPerdedor();
                          }else{
                            $matriz[$row->getPerdedor()->getId()]['total_perdedor'] =  $row->getApuesta()->getMonto() - $row->getSaldoPerdedor();
                            $matriz[$row->getPerdedor()->getId()]['nombre'] = $row->getPerdedor()->getNickname();

                            $matriz[$row->getPerdedor()->getId()]['saldo_ilimitado'] = $row->getPerdedor()->getSaldoIlimitado();
                          }  

                          $total_casa += $row->getSaldoCasa(); 
                          $hipodromo = $row->getApuesta()->getCarrera()->getHipodromo()->getNombre(); 
                      }
                      
                

                      
                      $i=0;
                      $matriz_xls = NULL;
                       $matriz_xls = array();  
                      foreach($matriz as $item){
                        $total_ganador = isset($item['total_ganador']) ? $item['total_ganador'] : 0;
                        $total_perdedor = isset($item['total_perdedor']) ? $item['total_perdedor'] : 0;
                        
                        //echo $item['nombre'].'-'.$total_ganador .'-'.$total_perdedor.'='.($total_ganador - $total_perdedor).'<br>';

                        $matriz_xls[$i]['total'] = $total_ganador - $total_perdedor;
                        $matriz_xls[$i]['nombre'] = $item['nombre'];
                        $matriz_xls[$i]['saldo_ilimitado'] = $item['saldo_ilimitado'];
                        $i++;
                      }

                    //  echo '-------------------------------------'.'<br>';

                     //burbuja
                     $longitud = count($matriz_xls);
                        for ($i = 0; $i < $longitud; $i++) {
                            for ($j = 0; $j < $longitud - 1; $j++) {
                                if (abs($matriz_xls[$j]['total']) < abs($matriz_xls[$j + 1]['total'])) {
                                    $temporal = $matriz_xls[$j];
                                    $matriz_xls[$j] = $matriz_xls[$j + 1];
                                    $matriz_xls[$j + 1] = $temporal;
                                }
                            }
                        }

                     /* echo '<pre>';
                        print_r($matriz_xls);
                      echo '</pre>';
                     // exit;*/

                if($total_casa){

                
                   if($h>0){
                        $sheet_index++; //lleva el control de las hojas  
                        $spreadsheet->createSheet();
                        $spreadsheet->setActiveSheetIndex($sheet_index);
                        $sheet = $spreadsheet->getActiveSheet();
                        $sheet->setTitle($hipodromo); 
                                         
                   }else{
                     $sheet = $spreadsheet->getActiveSheet();
                     $sheet->setTitle("TOTALES");                    
                   }                    
                    /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
                   // $sheet = $spreadsheet->getActiveSheet();
                    
                    //columnas
                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);

                    $i=1;
                    $sheet->setCellValue('A'.$i, 'GANADORES');       
                    $sheet->setCellValue('B'.$i, 'MONTO');          
                    $sheet->setCellValue('D'.$i, 'PERDEDORES');
                    $sheet->setCellValue('E'.$i, 'MONTO');
                    
                    $i=3;
                    $j=3;
              
                    $bold = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];

                    /* echo '<pre>';
                        print_r($matriz_xls);
                      echo '</pre>';
                    */
                        foreach ($matriz_xls as $item) {  
                          if($item['total']>0){
                            if ($item['saldo_ilimitado']) {
                                $sheet->getStyle('A'.$i)->applyFromArray($bold);
                                $item['nombre'] .= '(ilim)';
                            }
                            $sheet->setCellValue('A'.$i, $item['nombre']);           
                            $sheet->setCellValue('B'.$i, $item['total']);
                            $i++;    
                          }else{
                            if ($item['saldo_ilimitado']) {
                                $sheet->getStyle('D'.$j)->applyFromArray($bold);
                                $item['nombre'] .= '(ilim)';
                            }
                            $sheet->setCellValue('D'.$j, $item['nombre']);
                            $sheet->setCellValue('E'.$j, $item['total']);
                            $j++;           
                          }                        
                          
                        }

                    $x = ($i > $j) ? $i : $j;        
               

                    $sheet->getStyle('A1:E1')->applyFromArray($bold);
                    $sheet->getStyle('A'.($x+4).':E'.($x+4))->applyFromArray($bold);
                    $sheet->getStyle('A'.($x+2).':E'.($x+2))->applyFromArray($bold);

                    $sheet->setCellValue('D'.($x+4), 'GANANCIA');
                    $sheet->setCellValue('E'.($x+4), $total_casa); 

                    $SUMRANGE = 'B3:B'.$i;
                    $sheet->setCellValue('A'.($x+2), 'TOTAL GANADOR');
                    $sheet->setCellValue('B'.($x+2) , "=SUBTOTAL(109,$SUMRANGE)");

                    $SUMRANGE = 'E3:E'.$i;
                    $sheet->setCellValue('D'.($x+2), 'TOTAL PERDEDOR');
                    $sheet->setCellValue('E'.($x+2) , "=SUBTOTAL(109,$SUMRANGE)");   

                    $sheet->setCellValue('A'.($x+4), $fecha1);
                    $sheet->setCellValue('B'.($x+4), $fecha2);  
                       
                    $sheet->getStyle('E3:E'.($x+2))->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    
                    
               }//if total_casa - por las hojas en blanco cuando no hay registros en hipodromo  
                    
            }         

       // exit;
        //$sheet->setAutoFilter('A1:I'.$i);
        $spreadsheet->setActiveSheetIndex(0);
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'totales'.$fecha1.'-'.$fecha2.'.xlsx';
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
