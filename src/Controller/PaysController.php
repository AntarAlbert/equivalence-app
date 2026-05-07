<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Form\PaysType;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/pays', name: 'admin_pays_')]
final class PaysController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(PaysRepository $paysRepository): Response
    {
        return $this->render('pays/index.html.twig', [
            'pays' => $paysRepository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PaysRepository $paysRepository): Response
    {
        $pays = new Pays();
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateUniqueFields($pays, $paysRepository, $form);
            if ($form->isValid()) {
                $entityManager->persist($pays);
                $entityManager->flush();
                $this->addFlash('success', sprintf('Le pays "%s" a été créé avec succès.', $pays->getNomFrFr()));
                return $this->redirectToRoute('admin_pays_index');
            }
        }

        return $this->render('pays/new.html.twig', [
            'form' => $form->createView(),
            'pays' => $pays,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Pays $pays): Response
    {
        return $this->render('pays/show.html.twig', [
            'pays' => $pays,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pays $pays, EntityManagerInterface $entityManager, PaysRepository $paysRepository): Response
    {
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateUniqueFields($pays, $paysRepository, $form, $pays->getId());
            if ($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', sprintf('Le pays "%s" a été modifié avec succès.', $pays->getNomFrFr()));
                return $this->redirectToRoute('admin_pays_show', ['id' => $pays->getId()]);
            }
        }

        return $this->render('pays/edit.html.twig', [
            'form' => $form->createView(),
            'pays' => $pays,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Pays $pays, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_pays_' . $pays->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Vérification : présence d'établissements liés (et indirectement de diplômes)
        if ($pays->getEtablissements()->count() > 0) {
            $this->addFlash('danger', 'Impossible de supprimer ce pays car des établissements y sont associés.');
            return $this->redirectToRoute('admin_pays_show', ['id' => $pays->getId()]);
        }

        $nomPays = $pays->getNomFrFr();
        $entityManager->remove($pays);
        $entityManager->flush();
        $this->addFlash('success', sprintf('Le pays "%s" a été supprimé.', $nomPays));
        return $this->redirectToRoute('admin_pays_index');
    }

    private function validateUniqueFields(Pays $pays, PaysRepository $repository, FormInterface $form, ?int $currentId = null): void
    {
        // Alpha-2
        $existingAlpha2 = $repository->findOneByAlpha2($pays->getAlpha2());
        if ($existingAlpha2 !== null && $existingAlpha2->getId() !== $currentId) {
            $form->get('alpha2')->addError(new FormError('Ce code alpha-2 existe déjà.'));
        }

        // Alpha-3
        $existingAlpha3 = $repository->findOneByAlpha3($pays->getAlpha3());
        if ($existingAlpha3 !== null && $existingAlpha3->getId() !== $currentId) {
            $form->get('alpha3')->addError(new FormError('Ce code alpha-3 existe déjà.'));
        }

        // Code numérique
        $existingCode = $repository->findOneByCode($pays->getCode());
        if ($existingCode !== null && $existingCode->getId() !== $currentId) {
            $form->get('code')->addError(new FormError('Ce code numérique existe déjà.'));
        }
    }
}
