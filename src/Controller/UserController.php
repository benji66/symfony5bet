<?php

namespace App\Controller;

use App\Entity\User;
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
 * Require ROLE_ADMIN for *every* controller method in this class.
 *
 * @IsGranted("ROLE_COORDINADOR")
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator,  Request $request): Response
    {    
        

      //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');        //searchForm
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        /*$logged_user = $this->getUser();
        echo  $logged_user->getGerenciaPermiso()->getId().'--------//-----';
        exit;*/

        $allRowsQuery = $userRepository->createQueryBuilder('a')
            //->where('p.status != :status')
            //->setParameter('status', 'canceled')
            ; 


        if ($request->query->get("user")) {
            $val = $request->query->get("user");

            $user->setEmail($val['email']);
            $allRowsQuery = $allRowsQuery
            ->andWhere('a.email LIKE :email')
            ->setParameter('email', '%'.$val['email'].'%');
        }

        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery(); 


      
         
        
        // Paginate the results of the query
        $rows = $paginator->paginate(
            // Doctrine Query, not results
            $allRowsQuery,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            30
        );
 

        // Render the twig view
        return $this->render('user/index.html.twig', [
            'users' => $rows,
            'user' => $user,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,  UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

           

            $entityManager = $this->getDoctrine()->getManager();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager->persist($user);
            $entityManager->flush();

             $this->addFlash(
            'success',
            'Your changes were saved!'
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
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {

      
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
            'Your changes were saved!'
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
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Your changes were saved!'
            );
        }

        return $this->redirectToRoute('user_index');
    }

     /**
     * @Route("/pdf", name="user_pdf", methods={"GET"})
     */
     public function getPdf(UserRepository $userRepository, Request $request)
     {        // Configure Dompdf according to your needs
     
             //searchForm   
        
        
        $allRowsQuery = $userRepository->createQueryBuilder('a')
            //->where('p.status != :status')
            //->setParameter('status', 'canceled')
            ; 

        if ($request->query->get("user")) {

            $val = $request->query->get("user");

            $allRowsQuery = $allRowsQuery
            ->andWhere('a.email LIKE :email')
            ->setParameter('email', '%'.$val['email'].'%');
        }

        // Find all the data, filter your query as you need
         $allRowsQuery = $allRowsQuery->getQuery()->getResult(); 
       
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        //$html = $this->renderView($vista, $registros);

        $html = $this->renderView('user/pdf.html.twig', [
            'users' => $allRowsQuery
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
