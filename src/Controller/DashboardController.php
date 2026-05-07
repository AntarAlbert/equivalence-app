<?php

namespace App\Controller;

use App\Repository\EquivalenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        EquivalenceRepository $repo
    ): Response {

        return $this->render('dashboard/index.html.twig', [
            'total' => count($repo->findAll()),
            'approved' => count(
                $repo->findBy(['status' => 'approved'])
            ),
            'rejected' => count(
                $repo->findBy(['status' => 'rejected'])
            ),
        ]);
    }
}
