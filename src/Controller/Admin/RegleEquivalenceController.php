<?php
// src/Controller/Admin/RegleEquivalenceController.php

namespace App\Controller\Admin;

use App\Entity\Diplome;
use App\Entity\RegleEquivalence;
use App\Form\RegleEquivalenceType;
use App\Repository\RegleEquivalenceRepository;
use App\Service\RegleEquivalenceManager;
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
    public function index(
        RegleEquivalenceRepository $repository
    ): Response {
        return $this->render(
            'admin/regle_equivalence/index.html.twig',
            [
                'regles' => $repository->findVisible(),
            ]
        );
    }

    #[Route('/new/{diplome}', name: 'admin_regle_equivalence_new', methods: ['GET', 'POST'], defaults: ['diplome' => null])]
    public function new(
        ?Diplome $diplome,
        Request $request,
        RegleEquivalenceManager $manager
    ): Response {

        $regle = new RegleEquivalence();

        if ($diplome instanceof Diplome) {
            $regle->setDiplome($diplome);
        }

        $form = $this->createForm(
            RegleEquivalenceType::class,
            $regle
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {

            try {

                $manager->save($regle);

                $this->addFlash(
                    'success',
                    'Règle créée avec succès.'
                );

                return $this->redirectToRoute(
                    'admin_regle_equivalence_index'
                );

            } catch (\Throwable $exception) {

                $this->addFlash(
                    'danger',
                    $exception->getMessage()
                );
            }
        }

        return $this->render(
            'admin/regle_equivalence/form.html.twig',
            [
                'form' => $form->createView(),
                'regle' => $regle,
                'isEdit' => false,
                'cancel_path' => $this->generateUrl(
                    'admin_regle_equivalence_index'
                ),
            ]
        );
    }

    #[Route('/{id}/edit', name: 'admin_regle_equivalence_edit', methods: ['GET', 'POST'])]
    public function edit(
        RegleEquivalence $regle,
        Request $request,
        EntityManagerInterface $em,
        RegleEquivalenceRepository $repository,
        LoggerInterface $logger
    ): Response {

        if ($regle->isDeleted()) {

            $this->addFlash(
                'warning',
                'Cette règle a été archivée.'
            );

            return $this->redirectToRoute(
                'admin_regle_equivalence_index'
            );
        }

        $originalActif = $regle->isActif();

        $form = $this->createForm(
            RegleEquivalenceType::class,
            $regle
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {

            $diplome = $regle->getDiplome();
            $dateDebut = $regle->getDateDebut();
            $dateFin = $regle->getDateFin();

            if (
                $repository->hasOverlap(
                    $diplome,
                    $dateDebut,
                    $dateFin,
                    $regle->getId()
                )
            ) {

                $this->addFlash(
                    'danger',
                    'Chevauchement de période avec une autre règle.'
                );

                return $this->renderForm(
                    $form,
                    $regle
                );
            }

            if (
                $regle->isActif()
                && !$originalActif
            ) {

                $activeRule = $repository->findActiveRule(
                    $diplome,
                    $dateDebut
                );

                if (
                    $activeRule instanceof RegleEquivalence
                    && $activeRule !== $regle
                ) {
                    $activeRule->setActif(false);
                }
            }

            try {

                $em->flush();

                $logger->info(
                    'Règle modifiée',
                    [
                        'id' => $regle->getId(),
                        'user' => $this->getUser()?->getUserIdentifier(),
                    ]
                );

                $this->addFlash(
                    'success',
                    'Règle mise à jour.'
                );

                return $this->redirectToRoute(
                    'admin_regle_equivalence_index'
                );

            } catch (\Throwable $exception) {

                $logger->error(
                    'Erreur modification règle',
                    [
                        'id' => $regle->getId(),
                        'message' => $exception->getMessage(),
                    ]
                );

                $this->addFlash(
                    'danger',
                    'Erreur lors de la modification.'
                );
            }
        }

        return $this->render(
            'admin/regle_equivalence/form.html.twig',
            [
                'form' => $form->createView(),
                'regle' => $regle,
                'isEdit' => true,
                'cancel_path' => $this->generateUrl(
                    'admin_regle_equivalence_index'
                ),
            ]
        );
    }

    #[Route('/{id}/delete', name: 'admin_regle_equivalence_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        RegleEquivalence $regle,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {

        $token = (string) $request->request->get('_token');

        if (
            !$this->isCsrfTokenValid(
                'delete_regle_' . $regle->getId(),
                $token
            )
        ) {

            $this->addFlash(
                'danger',
                'Token CSRF invalide.'
            );

            return $this->redirectToRoute(
                'admin_regle_equivalence_index'
            );
        }

        try {

            if ($regle->isDeleted()) {

                $this->addFlash(
                    'warning',
                    'Cette règle est déjà archivée.'
                );

                return $this->redirectToRoute(
                    'admin_regle_equivalence_index'
                );
            }

            $regle->softDelete();

            $em->flush();

            $logger->info(
                'Règle archivée',
                [
                    'id' => $regle->getId(),
                    'user' => $this->getUser()?->getUserIdentifier(),
                ]
            );

            $this->addFlash(
                'success',
                'Règle archivée avec succès.'
            );

        } catch (\Throwable $exception) {

            $logger->error(
                'Erreur archivage règle',
                [
                    'id' => $regle->getId(),
                    'message' => $exception->getMessage(),
                ]
            );

            $this->addFlash(
                'danger',
                'Erreur lors de l’archivage.'
            );
        }

        return $this->redirectToRoute(
            'admin_regle_equivalence_index'
        );
    }

    private function renderForm(
        $form,
        ?RegleEquivalence $regle = null
    ): Response {

        return $this->render(
            'admin/regle_equivalence/form.html.twig',
            [
                'form' => $form->createView(),
                'regle' => $regle,
            ]
        );
    }
}
