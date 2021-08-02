<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/upload")
 */
class UploadController extends AbstractController
{

    /**
     * @Route("/delete", name="upload_delete")
     */
    public function delete(Request $request)
    {

        /*$repository = $this->getDoctrine()->getRepository(Archivo::class);

		$archivo = $repository->findOneByQquuid($request->request->get("qquuid"));
       

		$entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($archivo);
        $entityManager->flush();
 		
 		$uploader_config = $this->getParameter('oneup_uploader.config.gallery');
        
        @unlink($uploader_config['storage']['directory'].$archivo->getRuta());*/

        //return true;

        $archivo = $this->getParameter('kernel.project_dir').'/public/data/uploads/'.$request->get('filename');
        //echo $archivo;
        @unlink($archivo);

        $response = new Response();
		//$response->setContent($this->getParameter('kernel.project_dir').'/../data/uploads/'.$request->get('filename'));
		$response->setStatusCode(Response::HTTP_OK);


		// sets a HTTP response header
		$response->headers->set('Content-Type', 'text/html');

		// prints the HTTP headers followed by the content
		return $response;

    }
}
