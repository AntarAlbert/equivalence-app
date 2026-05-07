<?php
// src/Controller/CommissionController.php

namespace App\Controller;

use App\Entity\Equivalence;
use App\Repository\EquivalenceRepository;
use App\Security\Voter\EquivalenceVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/commission')]
#[IsGranted('ROLE_COMMISSION')]
class CommissionController extends AbstractController
{
    // Constantes pour éviter les chaînes magiques
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_IN_COMMITTEE = 'in_committee';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const DECISION_ACCEPTED = 'ACCEPTE';
    public const DECISION_REJECTED = 'REJETE';
    public const CLASSEMENT_EQUIVALENT = 'ÉQUIVALENT';

     public const CLASSEMENT_A2 = 'A2';

    #[Route('/', name: 'commission_dashboard', methods: ['GET'])]
    public function dashboard(
        EquivalenceRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // Pagination : 20 éléments par page
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        // Requêtes paginées pour chaque statut
        $submittedQuery = $repository->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', self::STATUS_SUBMITTED)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery();

        $inReviewQuery = $repository->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', self::STATUS_IN_REVIEW)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery();

        $committeeQuery = $repository->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', self::STATUS_IN_COMMITTEE)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery();

        $submitted = $paginator->paginate($submittedQuery, $page, $limit);
        $inReview   = $paginator->paginate($inReviewQuery, $page, $limit);
        $committee  = $paginator->paginate($committeeQuery, $page, $limit);

        // Derniers dossiers traités (avec pagination séparée)
        $lastDecidedQuery = $repository->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', [self::STATUS_APPROVED, self::STATUS_REJECTED])
            ->orderBy('e.updatedAt', 'DESC')
            ->getQuery();
        $lastDecided = $paginator->paginate($lastDecidedQuery, $page, 10);

        // Compteurs pour les badges
        $approvedCount = $repository->count(['status' => self::STATUS_APPROVED]);
        $rejectedCount = $repository->count(['status' => self::STATUS_REJECTED]);

        return $this->render('commission/dashboard.html.twig', [
            'submitted' => $submitted,
            'inReview' => $inReview,
            'committee' => $committee,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'lastDecided' => $lastDecided,
        ]);
    }

    #[Route('/dossier/{id}', name: 'commission_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Equivalence $equivalence): Response
    {
        // Voter 'VIEW' (permet de restreindre si besoin)
        $this->denyAccessUnlessGranted(EquivalenceVoter::VIEW, $equivalence);

        return $this->render('commission/show.html.twig', [
            'equivalence' => $equivalence
        ]);
    }

#[Route('/transition/{id}/{transition}', name: 'commission_transition', methods: ['POST'], requirements: [
        'id' => '\d+',
        'transition' => 'start_review|send_to_committee|approve|reject|ask_modification'
    ])]
    public function transition(
        Request $request,
        Equivalence $equivalence,
        string $transition,
        #[Autowire(service: 'state_machine.equivalence_state_machine')]
        WorkflowInterface $workflow,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {
        // CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('transition_' . $equivalence->getId(), $submittedToken)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('commission_dashboard');
        }

        // Vérifier que la transition est autorisée
        if (!$workflow->can($equivalence, $transition)) {
            $logger->warning('Transition refusée (commission)', [
                'dossier_id' => $equivalence->getId(),
                'transition' => $transition,
                'status' => $equivalence->getStatus(),
                'user' => $this->getUser()->getUserIdentifier()
            ]);
            $this->addFlash('danger', "Transition '$transition' non autorisée.");
            return $this->redirectToRoute('commission_show', ['id' => $equivalence->getId()]);
        }

        $oldStatus = $equivalence->getStatus();

        try {
            // Appliquer la transition
            $workflow->apply($equivalence, $transition);

            // Décisions automatiques (si besoin)
            if ($transition === 'approve') {
                $equivalence->setDecision(self::DECISION_ACCEPTED)
                           ->setClassement(self::CLASSEMENT_A2);
            } elseif ($transition === 'reject') {
                $equivalence->setDecision(self::DECISION_REJECTED);
            }

            $em->flush();

            $logger->info('Transition appliquée (commission)', [
                'dossier_id' => $equivalence->getId(),
                'transition' => $transition,
                'from' => $oldStatus,
                'to' => $equivalence->getStatus(),
                'user' => $this->getUser()->getUserIdentifier()
            ]);

            $this->addFlash('success', "Transition '$transition' effectuée.");
        } catch (\Throwable $e) {
            $logger->error('Erreur workflow (commission)', [
                'error' => $e->getMessage(),
                'transition' => $transition,
                'dossier_id' => $equivalence->getId()
            ]);
            $this->addFlash('danger', 'Erreur technique lors de la transition.');
        }

        return $this->redirectToRoute('commission_show', ['id' => $equivalence->getId()]);
    }
}
