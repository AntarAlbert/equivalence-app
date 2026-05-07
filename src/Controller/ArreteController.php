<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArreteController extends AbstractController
{
    #[Route('/arrete', name: 'app_arrete')]
    public function index(): Response
    {
        return $this->render('arrete/index.html.twig', [
            'controller_name' => 'ArreteController',
        ]);
    }
}
