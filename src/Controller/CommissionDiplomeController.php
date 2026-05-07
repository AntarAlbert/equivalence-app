<?php

namespace App\Controller;

use App\Entity\Diplome;
use App\Form\RegleEquivalenceType;
use App\Repository\DiplomeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/commission/diplome')]
#[IsGranted('ROLE_COMMISSION')]
class CommissionDiplomeController extends AbstractController
{
    #[Route('/', name: 'commission_diplome_index', methods: ['GET'])]
    public function index(DiplomeRepository $repo): Response
    {
        $pendingDiplomes = $repo->findBy(['validationStatus' => Diplome::STATUS_PENDING]);
        return $this->render('commission/diplome_list.html.twig', [
            'pending' => $pendingDiplomes,
        ]);
    }

    #[Route('/{id}/approve', name: 'commission_diplome_approve', methods: ['POST'])]
    public function approve(Diplome $diplome, EntityManagerInterface $em, WorkflowInterface $diplomeWorkflow, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('approve_diplome_' . $diplome->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('commission_diplome_index');
        }

        try {
            $diplomeWorkflow->apply($diplome, 'approve');
            $diplome->setApprovedBy($this->getUser());
            $diplome->setApprovedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Diplôme approuvé avec succès.');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur lors de l’approbation : ' . $e->getMessage());
        }

        return $this->redirectToRoute('commission_diplome_index');
    }

    #[Route('/{id}/reject', name: 'commission_diplome_reject', methods: ['POST'])]
    public function reject(Diplome $diplome, EntityManagerInterface $em, WorkflowInterface $diplomeWorkflow, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('reject_diplome_' . $diplome->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('commission_diplome_index');
        }

        try {
            $diplomeWorkflow->apply($diplome, 'reject');
            $diplome->setRejectedBy($this->getUser());
            $diplome->setRejectedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Diplôme rejeté.');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur lors du rejet : ' . $e->getMessage());
        }

        return $this->redirectToRoute('commission_diplome_index');
    }

    #[Route('/{id}/regle/new', name: 'commission_regle_new', methods: ['GET', 'POST'])]
    public function newRegle(Diplome $diplome, Request $request, EntityManagerInterface $em): Response
    {
        $regle = new \App\Entity\RegleEquivalence();
        $regle->setDiplome($diplome);

        $form = $this->createForm(RegleEquivalenceType::class, $regle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($regle);
            $em->flush();
            $this->addFlash('success', 'Règle d’équivalence créée.');
            return $this->redirectToRoute('commission_diplome_index');
        }

        return $this->render('commission/regle_form.html.twig', [
            'form' => $form->createView(),
            'diplome' => $diplome,
        ]);
    }
}
