<?php
// src/Controller/Admin/ArreteController.php

namespace App\Controller\Admin;

use App\Entity\Arrete;
use App\Entity\Equivalence;
use App\Form\ArreteType;
use App\Repository\ArreteRepository;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/arretes')]
#[IsGranted('ROLE_ADMIN')]
class ArreteController extends AbstractController
{
    #[Route('/', name: 'admin_arrete_index', methods: ['GET'])]
    public function index(ArreteRepository $repository): Response
    {
        return $this->render('admin/arrete/index.html.twig', [
            'arretes' => $repository->findAll(),
        ]);
    }

// src/Controller/Admin/ArreteController.php
#[Route('/new/{equivalence_id}', name: 'admin_arrete_new', methods: ['GET', 'POST'], defaults: ['equivalence_id' => null])]
public function new(Request $request, EntityManagerInterface $em, LoggerInterface $logger, ?int $equivalence_id = null): Response
{
    $arrete = new Arrete();

    if ($equivalence_id) {
        $equivalence = $em->getRepository(Equivalence::class)->find($equivalence_id);
        if ($equivalence) {
            // Vérifier si un arrêté existe déjà pour cette équivalence
            if ($equivalence->getArrete()) {
                $this->addFlash('danger', 'Un arrêté existe déjà pour ce dossier.');
                return $this->redirectToRoute('admin_arrete_show', ['id' => $equivalence->getArrete()->getId()]);
            }
            $arrete->setEquivalence($equivalence);
            // Numéro de l'arrêté = numéro du dossier
            $arrete->setNumeroArrete($equivalence->getNumeroDossier());
            $arrete->setTitre(sprintf(
                'Arrêté portant équivalence du diplôme de %s %s',
                $equivalence->getNom(),
                $equivalence->getPrenom()
            ));
            $arrete->setDateArrete(new \DateTimeImmutable());
        } else {
            $this->addFlash('warning', "L'équivalence demandée n'existe pas.");
            return $this->redirectToRoute('admin_arrete_index');
        }
    } else {
        // Création sans équivalence → on redirige vers une page de choix (à créer si besoin)
        $this->addFlash('warning', 'Veuillez sélectionner un dossier d’équivalence.');
        return $this->redirectToRoute('admin_arrete_index');
    }

    $form = $this->createForm(ArreteType::class, $arrete);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            $em->persist($arrete);
            $em->flush();
            $this->addFlash('success', 'Arrêté enregistré avec succès.');
            $logger->info('Arrêté créé', ['id' => $arrete->getId(), 'numero' => $arrete->getNumeroArrete()]);
            return $this->redirectToRoute('admin_arrete_show', ['id' => $arrete->getId()]);
        } catch (\Exception $e) {
            $logger->error('Erreur création arrêté', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de l’enregistrement.');
        }
    }

    return $this->render('admin/arrete/form.html.twig', [
        'form' => $form->createView(),
        'arrete' => $arrete,
    ]);
}
    #[Route('/{id}', name: 'admin_arrete_show', methods: ['GET'])]
    public function show(int $id, ArreteRepository $repository): Response
    {
        $arrete = $repository->find($id);
        if (!$arrete) {
            throw $this->createNotFoundException('Arrêté introuvable.');
        }
        return $this->render('admin/arrete/show.html.twig', [
            'arrete' => $arrete,
        ]);
    }

    #[Route('/{id}/pdf', name: 'admin_arrete_pdf', methods: ['GET'])]
    public function pdf(int $id, ArreteRepository $repository, PdfGenerator $pdfGenerator, LoggerInterface $logger): Response
    {
        $arrete = $repository->find($id);
        if (!$arrete) {
            throw $this->createNotFoundException('Arrêté introuvable.');
        }
        try {
            return $pdfGenerator->generateArreteFromArrete($arrete);
        } catch (\Exception $e) {
            $logger->error('Erreur génération PDF', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Impossible de générer le PDF.');
            return $this->redirectToRoute('admin_arrete_show', ['id' => $arrete->getId()]);
        }
    }

    #[Route('/{id}/edit', name: 'admin_arrete_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ArreteRepository $repository, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $arrete = $repository->find($id);
        if (!$arrete) {
            throw $this->createNotFoundException('Arrêté introuvable.');
        }

        $form = $this->createForm(ArreteType::class, $arrete);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Arrêté modifié.');
                $logger->info('Arrêté modifié', ['id' => $arrete->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
                return $this->redirectToRoute('admin_arrete_show', ['id' => $arrete->getId()]);
            } catch (\Exception $e) {
                $logger->error('Erreur modification arrêté', ['error' => $e->getMessage()]);
                $this->addFlash('danger', 'Erreur lors de la modification.');
            }
        }
        return $this->render('admin/arrete/form.html.twig', [
            'form' => $form->createView(),
            'arrete' => $arrete,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_arrete_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ArreteRepository $repository, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $arrete = $repository->find($id);
        if (!$arrete) {
            $this->addFlash('danger', 'Arrêté introuvable.');
            return $this->redirectToRoute('admin_arrete_index');
        }

        if (!$this->isCsrfTokenValid('delete' . $arrete->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_arrete_index');
        }

        try {
            $em->remove($arrete);
            $em->flush();
            $this->addFlash('success', 'Arrêté supprimé.');
            $logger->info('Arrêté supprimé', ['id' => $arrete->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
        } catch (\Exception $e) {
            $logger->error('Erreur suppression arrêté', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de la suppression.');
        }
        return $this->redirectToRoute('admin_arrete_index');
    }

    #[Route('/equivalence/{id}/redirect', name: 'admin_arrete_by_equivalence', methods: ['GET'])]
    public function redirectByEquivalence(
        Equivalence $equivalence,
        ArreteRepository $repository
    ): RedirectResponse {
        $arrete = $repository->findOneBy(['equivalence' => $equivalence]);

        if (!$arrete) {
            return $this->redirectToRoute('admin_arrete_new', ['equivalence_id' => $equivalence->getId()]);
        }

        return $this->redirectToRoute('admin_arrete_show', ['id' => $arrete->getId()]);
    }
}
