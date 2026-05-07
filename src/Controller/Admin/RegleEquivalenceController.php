<?php
// src/Controller/Admin/RegleEquivalenceController.php

namespace App\Controller\Admin;

use App\Entity\Diplome;
use App\Entity\RegleEquivalence;
use App\Form\RegleEquivalenceType;
use App\Repository\DiplomeRepository;
use App\Repository\RegleEquivalenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/regles-equivalence')]
#[IsGranted('ROLE_ADMIN')]
class RegleEquivalenceController extends AbstractController
{
    #[Route('/', name: 'admin_regle_equivalence_index', methods: ['GET'])]
    public function index(RegleEquivalenceRepository $repository): Response
    {
        $regles = $repository->findBy([], ['diplome' => 'ASC', 'dateDebut' => 'DESC']);
        return $this->render('admin/regle_equivalence/index.html.twig', [
            'regles' => $regles,
        ]);
    }

   #[Route('/new/{diplome?}', name: 'admin_regle_equivalence_new', methods: ['GET', 'POST'])]
public function new(Request $request, ?Diplome $diplome, EntityManagerInterface $em, RegleEquivalenceRepository $repo, LoggerInterface $logger): Response
{
    $regle = new RegleEquivalence();
    if ($diplome) {
        $regle->setDiplome($diplome);
    }
    $form = $this->createForm(RegleEquivalenceType::class, $regle);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $diplome = $regle->getDiplome();
        $debut = $regle->getDateDebut();
        $fin   = $regle->getDateFin();

        // Vérifier chevauchement
        if ($repo->hasOverlap($diplome, $debut, $fin)) {
            $this->addFlash('danger', 'Une autre règle existe déjà sur cette période pour ce diplôme.');
            return $this->renderForm($form);
        }

        // Si la règle est active, désactiver l'ancienne
        if ($regle->isActif()) {
            $activeRule = $repo->findActiveRule($diplome, $debut);
            if ($activeRule && $activeRule !== $regle) {
                $activeRule->setActif(false);
            }
        }

        try {
            $em->persist($regle);
            $em->flush();
            $logger->info('Règle d’équivalence créée', [
                'diplome' => $diplome->getId(),
                'user' => $this->getUser()->getUserIdentifier(),
            ]);
            $this->addFlash('success', 'Règle créée avec succès.');
            return $this->redirectToRoute('admin_regle_equivalence_index');
        } catch (\Exception $e) {
            $logger->error('Erreur création règle', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur technique lors de la création.');
        }
    }

    return $this->renderForm($form);
}

    #[Route('/{id}/edit', name: 'admin_regle_equivalence_edit', methods: ['GET', 'POST'])]
    public function edit(
        RegleEquivalence $regle,
        Request $request,
        EntityManagerInterface $em,
        RegleEquivalenceRepository $repo,
        LoggerInterface $logger
    ): Response {
        $originalActif = $regle->isActif();
        $form = $this->createForm(RegleEquivalenceType::class, $regle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diplome = $regle->getDiplome();
            $debut = $regle->getDateDebut();
            $fin   = $regle->getDateFin();

            // Vérifier les chevauchements (exclure la règle en cours)
            if ($repo->hasOverlap($diplome, $debut, $fin, $regle->getId())) {
                $this->addFlash('danger', 'Chevauchement de période avec une autre règle.');
                return $this->renderForm($form, $regle);
            }

            // Gestion de l’activation : si on active, désactiver l’ancienne active
            if ($regle->isActif() && !$originalActif) {
                $activeRule = $repo->findActiveRule($diplome, $debut);
                if ($activeRule && $activeRule !== $regle) {
                    $activeRule->setActif(false);
                }
            }

            try {
                $em->flush();
                $logger->info('Règle modifiée', ['id' => $regle->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
                $this->addFlash('success', 'Règle mise à jour.');
                return $this->redirectToRoute('admin_regle_equivalence_index');
            } catch (\Exception $e) {
                $logger->error('Erreur modification règle', ['error' => $e->getMessage()]);
                $this->addFlash('danger', 'Erreur lors de la modification.');
            }
        }

        return $this->renderForm($form, $regle);
    }

   #[Route('/{id}/delete', name: 'admin_regle_equivalence_delete', methods: ['POST'])]
    public function delete(Request $request, RegleEquivalence $regle, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        if (!$this->isCsrfTokenValid('delete_regle_' . $regle->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_regle_equivalence_index');
        }

        try {
            $regle->softDelete(); // utilise la méthode que nous avons ajoutée
            $em->flush();
            $logger->info('Règle archivée', ['id' => $regle->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
            $this->addFlash('success', 'Règle archivée avec succès.');
        } catch (\Exception $e) {
            $logger->error('Erreur archivage', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de l’archivage.');
        }

        return $this->redirectToRoute('admin_regle_equivalence_index');
    }
    private function renderForm($form, ?RegleEquivalence $regle = null): Response
    {
        return $this->render('admin/regle_equivalence/form.html.twig', [
            'form' => $form->createView(),
            'regle' => $regle,
        ]);
    }
}
