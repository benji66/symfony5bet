<?php
// src/App/EventListener/JWTAuthenticatedListener.php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
Class JWTAuthenticatedListener{ 

	/**
	 * @param JWTAuthenticatedEvent $event
	 *
	 * @return void
	 */
	public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
	{
	    $token = $event->getToken();
	    $payload = $event->getPayload();

	    //$token->setAttribute('uuid', $payload['uuid']);
	}

}	