<?php

namespace App\Fgimenez\PdfBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;



/**
 * @Route("/pdf")
 */
class DefaultController extends AbstractController
{
    
    /**
     * @Route("/", name="pdf_index", methods={"GET"})
     */
    public function index()
    {
        
        $this->getPdf('hello');
    }

	
    public function getPdf(String $contenido)
    {
        
    
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('@FgimenezPdf/mypdf.html.twig', [
            'html' => $contenido
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
        
    }
}