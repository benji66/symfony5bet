<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gerencia;
use App\Entity\Correccion;
use App\Entity\Carrera;
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_COORDINADOR")
 * @Route("/reporte")
 */
class ReporteController extends AbstractController
{
    /**
     * @Route("/", name="reporte_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {           

      $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA');          
        
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

        $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA'); 

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

        $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA'); 


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
            $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
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
        
        $sheet->setTitle("Correcciones en apuestas");

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

        $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA'); 

        //echo $request->query->get("fecha1");
        //exit;

        $repository = $this->getDoctrine()->getRepository(PagoPersonal::class);            
        
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

        $i=1;
        $sheet->setCellValue('A'.$i, 'FECHA');       
        $sheet->setCellValue('B'.$i, 'NICKNAME');
        $sheet->setCellValue('C'.$i, 'CONCEPTO');        
        $sheet->setCellValue('D'.$i, 'MONTO'); 
        $sheet->setCellValue('E'.$i, 'METODO DE PAGO');
        $sheet->setCellValue('F'.$i, 'REFERENCIA');
        $sheet->setCellValue('G'.$i, 'CREADO POR');
        $sheet->setCellValue('H'.$i, 'OBSERVACION');

        
        $i=3;
  
            foreach ($allRowsQuery as $row) {  
                $sheet->setCellValue('A'.$i, $row->getCreatedAt());           
                $sheet->setCellValue('B'.$i, $row->getPerfil()->getNickname());
                $sheet->setCellValue('C'.$i, $row->getConcepto());
                $sheet->setCellValue('D'.$i, $row->getMonto());
                $sheet->setCellValue('E'.$i, $row->getMetodoPago()->getNombre());
                $sheet->setCellValue('F'.$i, $row->getNumeroReferencia()); 
                $sheet->setCellValue('G'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('H'.$i, $row->getObservacion());             
                $i++;
            }
      
        
        $sheet->setTitle("Depositos directos");

        $sheet->setAutoFilter('A1:H'.$i);
        
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

        $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA'); 

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
        $sheet->setCellValue('B'.$i, 'NICKNAME');
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


    /**
     * @Route("/retirosaldo", name="reporte_retiro_saldo", methods={"GET"})
     */
    public function retiroSaldo(Request $request): Response
    {          

        $this->denyAccessUnlessGranted('ROLE_GERENCIA', null, 'User tried to access a page without having ROLE GERENCIA'); 
        
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
        $sheet->setCellValue('B'.$i, 'NICKNAME');          
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
                $sheet->setCellValue('E'.$i, $row->getMetodoPago()->getNombre() );
                $sheet->setCellValue('F'.$i, $row->getValidado());
                $sheet->setCellValue('G'.$i, $row->getValidadoBy());
                $sheet->setCellValue('H'.$i, $row->getCreatedBy()); 
                $sheet->setCellValue('I'.$i, $row->getObservacion());             
                $i++;
            }
      
        
        $sheet->setTitle("Depositos por saldo");

        $sheet->setAutoFilter('A1:F'.$i);
        
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
}
