<?php

namespace App\Controller;

use App\Entity\Diplome;
use App\Form\DiplomeType;
use App\Repository\DiplomeRepository;
use App\Security\Voter\DiplomeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/etablissement/diplome')]
#[IsGranted('ROLE_ETABLISSEMENT')]
class EtablissementDiplomeController extends AbstractController
{
    #[Route('/', name: 'etablissement_diplome_index', methods: ['GET'])]
    public function index(
        Request $request,
        DiplomeRepository $repository
    ): Response {

        $search = trim((string) $request->query->get('search', ''));

        $user = $this->getUser();

        $queryBuilder = $repository
            ->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->addSelect('e')
            ->where('d.proposedBy = :user')
            ->setParameter('user', $user)
            ->orderBy('d.createdAt', 'DESC');

        if ($search !== '') {

            $queryBuilder
                ->andWhere('
                    d.titre LIKE :search
                    OR e.nom LIKE :search
                ')
                ->setParameter('search', '%' . $search . '%');
        }

        $diplomes = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('etablissement/diplome_list.html.twig', [
            'diplomes' => $diplomes,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'etablissement_diplome_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {

        $user = $this->getUser();

        $diplome = new Diplome();

        // ==========================================
        // ETABLISSEMENT PAR DÉFAUT
        // ==========================================

        if (
            method_exists($user, 'getEtablissement')
            && $user->getEtablissement()
        ) {

            $diplome->setEtablissement(
                $user->getEtablissement()
            );

            $diplome->setEtablissementSource(
                $user->getEtablissement()
            );
        }

        // ==========================================
        // TRAÇABILITÉ
        // ==========================================

        $diplome->setProposedBy($user);

        $diplome->setCreatedBy($user);

        $diplome->setValidationStatus(
            Diplome::STATUS_PENDING
        );

        $form = $this->createForm(
            DiplomeType::class,
            $diplome,
            [
                'is_etablissement' => true,
            ]
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {

            try {

                $em->persist($diplome);

                $em->flush();

                $logger->info(
                    'Diplôme proposé par établissement',
                    [
                        'diplome_id' => $diplome->getId(),
                        'titre' => $diplome->getTitre(),
                        'user' => $user?->getUserIdentifier(),
                    ]
                );

                $this->addFlash(
                    'success',
                    'Le diplôme a été soumis à la commission.'
                );

                return $this->redirectToRoute(
                    'etablissement_diplome_index'
                );

            } catch (\Throwable $e) {

                $logger->error(
                    'Erreur création diplôme établissement',
                    [
                        'error' => $e->getMessage(),
                    ]
                );

                $this->addFlash(
                    'danger',
                    'Erreur lors de l’enregistrement.'
                );
            }
        }

        return $this->render(
            'etablissement/diplome_form.html.twig',
            [
                'form' => $form->createView(),
                'diplome' => $diplome,
                'isEdit' => false,
            ]
        );
    }

    #[Route('/{id}', name: 'etablissement_diplome_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        Diplome $diplome
    ): Response {

        $this->denyAccessUnlessGranted(
            DiplomeVoter::VIEW,
            $diplome
        );

        return $this->render(
            'etablissement/diplome_show.html.twig',
            [
                'diplome' => $diplome,
            ]
        );
    }

    #[Route('/{id}/edit', name: 'etablissement_diplome_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Diplome $diplome,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {

        $this->denyAccessUnlessGranted(
            DiplomeVoter::EDIT,
            $diplome
        );

        if (
            $diplome->getValidationStatus()
            !== Diplome::STATUS_PENDING
        ) {

            $this->addFlash(
                'warning',
                'Ce diplôme ne peut plus être modifié.'
            );

            return $this->redirectToRoute(
                'etablissement_diplome_index'
            );
        }

        $form = $this->createForm(
            DiplomeType::class,
            $diplome,
            [
                'is_etablissement' => true,
            ]
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {

            try {

                $diplome->setUpdatedBy(
                    $this->getUser()
                );

                $diplome->setUpdatedAt(
                    new \DateTimeImmutable()
                );

                $em->flush();

                $logger->info(
                    'Diplôme modifié',
                    [
                        'id' => $diplome->getId(),
                    ]
                );

                $this->addFlash(
                    'success',
                    'Diplôme modifié avec succès.'
                );

                return $this->redirectToRoute(
                    'etablissement_diplome_index'
                );

            } catch (\Throwable $e) {

                $logger->error(
                    'Erreur modification diplôme',
                    [
                        'id' => $diplome->getId(),
                        'error' => $e->getMessage(),
                    ]
                );

                $this->addFlash(
                    'danger',
                    'Erreur technique.'
                );
            }
        }

        return $this->render(
            'etablissement/diplome_form.html.twig',
            [
                'form' => $form->createView(),
                'diplome' => $diplome,
                'isEdit' => true,
            ]
        );
    }

    #[Route('/{id}/delete', name: 'etablissement_diplome_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        Request $request,
        Diplome $diplome,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {

        $this->denyAccessUnlessGranted(
            DiplomeVoter::EDIT,
            $diplome
        );

        if (
            !$this->isCsrfTokenValid(
                'delete_diplome_' . $diplome->getId(),
                $request->request->get('_token')
            )
        ) {

            $this->addFlash(
                'danger',
                'Token CSRF invalide.'
            );

            return $this->redirectToRoute(
                'etablissement_diplome_index'
            );
        }

        if (
            $diplome->getValidationStatus()
            !== Diplome::STATUS_PENDING
        ) {

            $this->addFlash(
                'warning',
                'Impossible de supprimer un diplôme déjà traité.'
            );

            return $this->redirectToRoute(
                'etablissement_diplome_index'
            );
        }

        try {

            $id = $diplome->getId();

            $em->remove($diplome);

            $em->flush();

            $logger->info(
                'Diplôme supprimé',
                [
                    'id' => $id,
                ]
            );

            $this->addFlash(
                'success',
                'Diplôme supprimé.'
            );

        } catch (\Throwable $e) {

            $logger->error(
                'Erreur suppression diplôme',
                [
                    'error' => $e->getMessage(),
                ]
            );

            $this->addFlash(
                'danger',
                'Erreur lors de la suppression.'
            );
        }

        return $this->redirectToRoute(
            'etablissement_diplome_index'
        );
    }
}
