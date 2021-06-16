<?php
namespace App\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;

use App\Entity\Archivo;
use App\Repository\ArchivoRepository;

class UploadListener
{
    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }
    
    public function onUpload(PostPersistEvent $event)
    {
        $request = $event->getRequest();
       
        $file = $event->getFile();
       

/*
        $archivo = new Archivo();
        $archivo->setRuta($file->getFileName());
        $archivo->setQquuid($request->get('qquuid'));        
        
        $this->om->persist($archivo);
        $this->om->flush();*/



        //if everything went fine
        $response = $event->getResponse();
        

        $response['success'] = true;
        $response['filename'] = $file->getFileName();

        $response['qquuid'] = $request->get('qquuid');
        return $response;
    }
}