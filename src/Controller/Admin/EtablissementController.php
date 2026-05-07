<?php

namespace App\Controller\Admin;

use App\Entity\Etablissement;
use App\Form\EtablissementType;
use App\Repository\EtablissementRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/etablissements')]
#[IsGranted('ROLE_ADMIN')]
class EtablissementController extends AbstractController
{
    #[Route('/', name: 'admin_etablissement_index', methods: ['GET'])]
    public function index(
        Request $request,
        EtablissementRepository $repository,
        PaysRepository $paysRepository
    ): Response {

        $search = trim((string) $request->query->get('search', ''));
        $paysId = $request->query->get('pays');

        $qb = $repository->createQueryBuilder('e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('p');

        if ($search !== '') {

            $qb
                ->andWhere('
                    LOWER(e.nom) LIKE :search
                    OR LOWER(e.ville) LIKE :search
                    OR LOWER(e.type) LIKE :search
                    OR LOWER(p.nomFrFr) LIKE :search
                ')
                ->setParameter(
                    'search',
                    '%' . mb_strtolower($search) . '%'
                );
        }

        if (!empty($paysId)) {

            $qb
                ->andWhere('p.id = :pays')
                ->setParameter('pays', $paysId);
        }

        $qb->orderBy('e.nom', 'ASC');

        $etablissements = $qb->getQuery()->getResult();

        $stats = [
            'total' => count($etablissements),

            'pays' => count(array_unique(array_filter(array_map(
                fn($e) => $e->getPays()?->getNomFrFr(),
                $etablissements
            )))),

            'diplomes' => array_reduce(
                $etablissements,
                fn($total, $e) => $total + $e->getDiplomes()->count(),
                0
            ),
        ];

        return $this->render('admin/etablissement/index.html.twig', [
            'etablissements' => $etablissements,
            'stats' => $stats,
            'search' => $search,
            'currentPays' => $paysId,
            'paysList' => $paysRepository->findBy([], ['nomFrFr' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_etablissement_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $etablissement = new Etablissement();

        $form = $this->createForm(EtablissementType::class, $etablissement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($etablissement);
            $em->flush();

            $this->addFlash('success', 'Établissement créé.');

            return $this->redirectToRoute('admin_etablissement_index');
        }

        return $this->render('admin/etablissement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_etablissement_edit')]
    public function edit(
        Etablissement $etablissement,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $form = $this->createForm(EtablissementType::class, $etablissement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Établissement modifié.');

            return $this->redirectToRoute('admin_etablissement_index');
        }

        return $this->render('admin/etablissement/edit.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_etablissement_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Etablissement $etablissement,
        EntityManagerInterface $em
    ): Response {

        if (
            !$this->isCsrfTokenValid(
                'delete_etablissement_' . $etablissement->getId(),
                $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Token CSRF invalide.');

            return $this->redirectToRoute('admin_etablissement_index');
        }

        if ($etablissement->getDiplomes()->count() > 0) {

            $this->addFlash(
                'warning',
                'Impossible de supprimer cet établissement car il possède des diplômes.'
            );

            return $this->redirectToRoute('admin_etablissement_index');
        }

        $em->remove($etablissement);
        $em->flush();

        $this->addFlash('success', 'Établissement supprimé.');

        return $this->redirectToRoute('admin_etablissement_index');
    }
}
