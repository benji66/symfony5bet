<?php
// src/EventListener/AuthenticationSuccessListener.php
namespace App\EventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;


Class AuthenticationSuccessListener{ 
	/**
	 * @param AuthenticationSuccessEvent $event
	 */
	public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
	{
	    
	    /*echo '----';
	    exit();*/

	    $data = $event->getData();
	    $user = $event->getUser();

	    if (!$user instanceof UserInterface) {
	        return;
	    }

	    $data['data'] = array(
	        'roles' => $user->getRoles(),
	    );

	    $event->setData($data);
	}

	
}