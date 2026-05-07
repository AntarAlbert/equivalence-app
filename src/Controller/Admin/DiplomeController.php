<?php
// src/Controller/Admin/DiplomeController.php

namespace App\Controller\Admin;

use App\Entity\Diplome;
use App\Form\DiplomeType;
use App\Repository\DiplomeRepository;
use App\Repository\EquivalenceRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/diplomes')]
#[IsGranted('ROLE_ADMIN')]
class DiplomeController extends AbstractController
{
#[Route('/', name: 'admin_diplome_index', methods: ['GET'])]
public function index(
    DiplomeRepository $repository,
    PaysRepository $paysRepository,
    PaginatorInterface $paginator,
    Request $request
): Response {

    $page = $request->query->getInt('page', 1);
    $limit = 20;

    $search = trim((string) $request->query->get('search', ''));
    $currentPays = trim((string) $request->query->get('pays', ''));

    // =====================================================
    // QUERY PRINCIPALE
    // =====================================================

    $qb = $repository->createQueryBuilder('d')
        ->leftJoin('d.etablissement', 'e')
        ->leftJoin('e.pays', 'p')
        ->leftJoin('d.reglesEquivalence', 'r')
        ->addSelect('e', 'p', 'r')
        ->orderBy('d.titre', 'ASC');

    if ($search !== '') {

        $qb
            ->andWhere('
                LOWER(d.titre) LIKE :search
                OR LOWER(d.organisme) LIKE :search
                OR LOWER(e.nom) LIKE :search
                OR LOWER(e.ville) LIKE :search
            ')
            ->setParameter(
                'search',
                '%' . mb_strtolower($search) . '%'
            );
    }

    if ($currentPays !== '') {

        $qb
            ->andWhere('p.id = :pays')
            ->setParameter('pays', $currentPays);
    }

    // =====================================================
    // PAGINATION
    // =====================================================

    $pagination = $paginator->paginate(
        $qb,
        $page,
        $limit
    );

    // =====================================================
    // STATISTIQUES
    // =====================================================

    $stats = [

        'totalDiplomes' => (int) $repository
            ->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult(),

        'totalEtablissements' => (int) $repository
            ->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->select('COUNT(DISTINCT e.id)')
            ->getQuery()
            ->getSingleScalarResult(),

        'totalPays' => (int) $repository
            ->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->leftJoin('e.pays', 'p')
            ->select('COUNT(DISTINCT p.id)')
            ->getQuery()
            ->getSingleScalarResult(),
    ];

    // =====================================================
    // RENDER
    // =====================================================

    return $this->render('admin/diplome/index.html.twig', [

        'diplomes' => $pagination,

        'search' => $search,
        'currentPays' => $currentPays,

        'paysList' => $paysRepository->findBy(
            [],
            ['nomFrFr' => 'ASC']
        ),

        'stats' => $stats,
    ]);
}
    #[Route('/new', name: 'admin_diplome_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $diplome = new Diplome();
        $form = $this->createForm(DiplomeType::class, $diplome);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($diplome);
                $em->flush();
                $this->addFlash('success', 'Diplôme créé avec succès.');
                $logger->info('Diplôme créé', ['id' => $diplome->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
                return $this->redirectToRoute('admin_diplome_index');
            } catch (\Exception $e) {
                $logger->error('Erreur création diplôme', ['error' => $e->getMessage()]);
                $this->addFlash('danger', 'Erreur lors de la création.');
            }
        }

        return $this->render('admin/diplome/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_diplome_show', methods: ['GET'])]
    public function show(Diplome $diplome): Response
    {
        return $this->render('admin/diplome/show.html.twig', [
            'diplome' => $diplome,
        ]);
    }

#[Route('/{id}/edit', name: 'admin_diplome_edit', methods: ['GET', 'POST'])]
public function edit(int $id, Request $request, EntityManagerInterface $em, LoggerInterface $logger, DiplomeRepository $repository): Response
{
    $diplome = $repository->findWithRelations($id);
    if (!$diplome) {
        throw $this->createNotFoundException('Diplôme non trouvé');
    }

    $form = $this->createForm(DiplomeType::class, $diplome);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            $em->flush();
            $this->addFlash('success', 'Diplôme modifié.');
            $logger->info('Diplôme modifié', ['id' => $diplome->getId(), 'user' => $this->getUser()->getUserIdentifier()]);
            return $this->redirectToRoute('admin_diplome_index');
        } catch (\Exception $e) {
            $logger->error('Erreur modification diplôme', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de la modification.');
        }
    }

    return $this->render('admin/diplome/edit.html.twig', [
        'form' => $form->createView(),
        'diplome' => $diplome,
    ]);
}

    #[Route('/{id}/delete', name: 'admin_diplome_delete', methods: ['POST'])]
    public function delete(Request $request, Diplome $diplome, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        if (!$this->isCsrfTokenValid('delete_diplome_' . $diplome->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_diplome_index');
        }

        // Vérifier si le diplôme est utilisé par des équivalences
        if ($diplome->getEquivalences()->count() > 0) {
            $this->addFlash('warning', 'Ce diplôme ne peut pas être supprimé car il est utilisé dans des dossiers d\'équivalence.');
            return $this->redirectToRoute('admin_diplome_index');
        }

        try {
            $id = $diplome->getId();
            $em->remove($diplome);
            $em->flush();
            $logger->info('Diplôme supprimé', ['id' => $id, 'user' => $this->getUser()->getUserIdentifier()]);
            $this->addFlash('success', 'Diplôme supprimé.');
        } catch (\Exception $e) {
            $logger->error('Erreur suppression diplôme', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de la suppression.');
        }

        return $this->redirectToRoute('admin_diplome_index');
    }

#[Route('/{id}/equivalences', name: 'admin_diplome_equivalences', methods: ['GET'])]
public function equivalences(
    Diplome $diplome,
    Request $request,
    EquivalenceRepository $equivalenceRepository,
    PaginatorInterface $paginator
): Response {
    $page = $request->query->getInt('page', 1);
    $filters = [
        'statut' => $request->query->get('statut'),
        'nom' => $request->query->get('nom'),
        'numero' => $request->query->get('numero'),
    ];
    $pagination = $equivalenceRepository->paginateForDiplome($diplome->getId(), $paginator, $page, $filters);

    // Statistiques
    $stats = [
    'total' => $pagination->getTotalItemCount(),
    'draft' => $equivalenceRepository->count(['diplomeReference' => $diplome, 'status' => 'draft']),
    'submitted' => $equivalenceRepository->count(['diplomeReference' => $diplome, 'status' => 'submitted']),
    'approved' => $equivalenceRepository->count(['diplomeReference' => $diplome, 'status' => 'approved']),
    'rejected' => $equivalenceRepository->count(['diplomeReference' => $diplome, 'status' => 'rejected']),
];

    return $this->render('admin/diplome/equivalences.html.twig', [
        'diplome' => $diplome,
        'pagination' => $pagination,
        'stats' => $stats,
        'filters' => $filters,
    ]);
}
}
