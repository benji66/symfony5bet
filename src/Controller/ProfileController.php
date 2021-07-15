<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Perfil;
use App\Entity\AdjuntoPago;
use App\Entity\ApuestaDetalle;

use App\Form\UserType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Require ROLE_USER for *every* controller method in this class.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("/", name="profile_show", methods={"GET","POST"})
     */
    public function show(Request $request): Response
    {
       // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        //$user->getPerfil()->getAdjuntoPagos();

        $pagos = $this->getDoctrine()->getRepository(AdjuntoPago::class)->findById15Dias($user->getPerfil()->getId());

        $i=0;
        $time=array();
        foreach ($pagos as $row) {
            $time[$i]['tipo'] = 'pago';
            
            if($row->getValidado()){
                $time[$i]['verdadero'] = true;
                $time[$i]['validado'] = 'Aprobado';
            }else{
                $time[$i]['verdadero'] = false;
                $time[$i]['validado'] = 'Rechazado';
            }

            $time[$i]['iclass'] = 'fas fa-dollar-sign bg-green';

            $time[$i]['fecha'] = $row->getUpdatedBy();
            $time[$i]['mensaje'] = 'Su pago con el numero de referencia '.$row->getNumeroReferencia().' ha sido '.$time[$i]['validado'];
            $time[$i]['observacion'] = $row->getObservacion();
            $i++;  
        }

        
        //$apuestas = $this->getDoctrine()->getRepository(ApuestaDetalle::class)->findById15Dias($this->getUser()->getPerfil()->getId());



        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'times' => $time,                     
        ]);
    }

    /**
     * @Route("/update", name="profile_update", methods={"GET","POST"})
     */
    public function update(Request $request): Response
    {

                // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); 

      
        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        //return new Response('Well hi there '.$user->getPerfil()->getNombre());
        return $this->render('profile/update.html.twig', [
            'user' => $user,
            'perfils' => $user->getPerfils(),
           
        ]);
    } 

    /**
     * @Route("/new", name="profile_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfil = new Perfil();
       
        $user = $this->getUser(); 
        $perfil->setUsuario($user);

        $form = $this->createForm(ProfileType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $perfil->setActivo(true);            

            $entityManager->persist($perfil);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
            );

            return $this->redirectToRoute('profile_update');
        }

        return $this->render('profile/new.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }   

    /**
     * @Route("/{id}/change", name="profile_change", methods={"GET","POST"})
     */
    public function change(Request $request, Perfil $perfil): Response
    {

            $entityManager = $this->getDoctrine()->getManager();        
            
          
            $perfil->getUsuario()->setPerfil($perfil); 
            $perfil->getUsuario()->setRoles($perfil->getRoles());

            //$perfil->getUsuario();
            $entityManager->persist($perfil);
            $entityManager->flush();

           $this->addFlash(
            'success',
            'Perfil cambiado!'
            );

            return $this->redirectToRoute('app_logout');
       
    }

    /**
     * @Route("/api/ProfileSearch", name="profile_search", methods={"GET","POST"})
     */
    public function apiSearch(Request $request): Response
    {

          $user = $this->getUser(); 
          $perfiles = array();
          
          //echo '---66------'.$request->get("phrase").$user->getGerencia()->getId();

			
	          $perfils = $this->getDoctrine()->getRepository(Perfil::class)->findByGerencia($user->getPerfil()->getGerencia()->getId());

	          $i = 0;
	          foreach($perfils as $perfil){
	          	$perfiles[$i]['id'] = $perfil->getId();
	          	$perfiles[$i]['nickname'] = $perfil->getNickname();
	          	$perfiles[$i]['saldo'] = $perfil->getSaldo();

	          	$perfiles[$i]['nombre'] = $perfil->getUsuario()->getNombre();
	          	$perfiles[$i]['apellido'] = $perfil->getUsuario()->getApellido();
	          	$i++;
	          }
	      	
	      /*  echo '<pre>';
	        	print_r($perfiles);
	        echo '</pre>';	         
          exit;*/
          


        $response = new JsonResponse();
		    $response->setData($perfiles);

          //$response->setData([0=>['nombre' =>  $user->getNombre(), 'message' => 'status_comlpetado_200', 'code' => Response::HTTP_OK]]);

        return $response;
    }    


}
