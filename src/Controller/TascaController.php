<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/tasca")
 */
class TascaController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/resumen", name="tasca_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('tasca/index.html.twig', [
            'controller_name' => 'TascaController',
        ]);
    }
}
