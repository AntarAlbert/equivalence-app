<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Equivalence;
use App\Form\ConfirmationCodeType;
use App\Form\EquivalenceType;
use App\Repository\EquivalenceRepository;
use App\Repository\PaysRepository;
use App\Security\Voter\EquivalenceVoter;
use App\Service\CodeSender;
use App\Service\DocumentUploader;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/equivalence')]
class EquivalenceController extends AbstractController
{
    // Constantes identiques à celles que vous aviez
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_IN_COMMITTEE = 'in_committee';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const DECISION_ACCEPTED = 'ACCEPTE';
    public const DECISION_REJECTED = 'REFUSE';
    public const CLASSEMENT_A2 = 'A2';

    public const SENSITIVE_TRANSITIONS = ['submit', 'approve', 'reject', 'ask_modification'];
    public function __construct(
        private readonly EntityManagerInterface $entityManager,  ) {
    }

    // INDEX (avec filtre CANDIDAT)
    #[Route('/', name: 'equivalence_index', methods: ['GET'])]
    public function index(
        EquivalenceRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $search = trim((string) $request->query->get('search', ''));
        $currentStatus = trim((string) $request->query->get('status', ''));

        $queryBuilder = $repository->createQueryBuilder('e')
            ->leftJoin('e.diplomeReference', 'd')
            ->leftJoin('d.etablissement', 'et')
            ->leftJoin('et.pays', 'p')
            ->addSelect('d', 'et', 'p')
            ->orderBy('e.createdAt', 'DESC');

        // SEARCH
        if ($search !== '') {
            $queryBuilder
                ->andWhere('
                    e.numeroDossier LIKE :search OR
                    e.nom LIKE :search OR
                    e.prenom LIKE :search OR
                    e.email LIKE :search OR
                    d.titre LIKE :search OR
                    et.nom LIKE :search OR
                    p.nomFrFr LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // FILTRE STATUT
        if ($currentStatus !== '') {
            $queryBuilder
                ->andWhere('e.status = :status')
                ->setParameter('status', $currentStatus);
        }

        // IMPORTANT : candidat ne voit que ses dossiers
        if ($this->isGranted('ROLE_CANDIDAT') &&
            !$this->isGranted('ROLE_AGENT') &&
            !$this->isGranted('ROLE_COMMISSION') &&
            !$this->isGranted('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('e.user = :user')
                ->setParameter('user', $this->getUser());
        }

        $pagination = $paginator->paginate($queryBuilder, $page, $limit);

        return $this->render('equivalence/index.html.twig', [
            'items' => $pagination,
            'search' => $search,
            'currentStatus' => $currentStatus,
        ]);
    }

    // NOUVEAU DOSSIER – accès limité aux candidats
    #[Route('/new', name: 'equivalence_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CANDIDAT')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        DocumentUploader $uploader,
        LoggerInterface $logger,
        EquivalenceRepository $repository,
        PaysRepository $paysRepository
    ): Response {
        $equivalence = new Equivalence();
        $user = $this->getUser();

        if ($user) {
            $equivalence->setUser($user);
            $equivalence->setEmail($user->getUserIdentifier());
        }

        $madagascar = $paysRepository->findOneByAlpha2('MG');
        if ($madagascar) {
            $equivalence->setNationalite($madagascar);
        }

        $form = $this->createForm(EquivalenceType::class, $equivalence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentsMapping = [
                'diplomaFile' => 'DIPLOME',
                'transcriptFile' => 'RELEVE',
                'identityFile' => 'CIN'
            ];
            $hasErrors = false;

            foreach ($documentsMapping as $field => $type) {
                $file = $form->get($field)->getData();
                if (!$file instanceof UploadedFile) {
                    $this->addFlash('warning', "Le document $type est obligatoire.");
                    $hasErrors = true;
                    continue;
                }
                if (!$file->isValid()) {
                    $this->addFlash('danger', "Fichier $type invalide (code {$file->getError()}).");
                    $hasErrors = true;
                    continue;
                }
                try {
                    $document = new Document();
                    $uploader->upload($file, $document, $type);
                    $equivalence->addDocument($document);
                } catch (\Exception $e) {
                    $logger->error('Upload échoué', ['type' => $type, 'error' => $e->getMessage()]);
                    $this->addFlash('danger', "Erreur upload $type : {$e->getMessage()}");
                    $hasErrors = true;
                }
            }

            if ($hasErrors) {
                return $this->render('equivalence/new.html.twig', ['form' => $form->createView()]);
            }

            $numeroDossier = $repository->getNextNumeroDossierForCurrentYear();
            $equivalence->setNumeroDossier($numeroDossier);

            // Remplissage anciens champs pour rétrocompatibilité
            if ($equivalence->getDiplomeReference()) {
                $diplomeRef = $equivalence->getDiplomeReference();
                $equivalence->setDiplome($diplomeRef->getTitre() ?? '');
                if ($diplomeRef->getEtablissement()) {
                    $etab = $diplomeRef->getEtablissement();
                    $equivalence->setUniversite($etab->getNom() ?? '');
                    $equivalence->setPays($etab->getPays()?->getNomFrFr() ?? '');
                }
            }

            // Validation CNI selon âge (même code)
            $dateNaissance = $equivalence->getDateNaissance();
            $age = null;
            if ($dateNaissance) {
                $age = (new \DateTimeImmutable())->diff($dateNaissance)->y;
            }
            if ($age !== null && $age >= 18) {
                if (empty($equivalence->getCni()) || empty($equivalence->getCniDateDelivrance()) || empty($equivalence->getCniLieuDelivrance())) {
                    $this->addFlash('danger', 'Pour un majeur, le numéro CNI, la date et le lieu de délivrance sont obligatoires.');
                    return $this->render('equivalence/new.html.twig', ['form' => $form->createView()]);
                }
            } else {
                $equivalence->setCni(null);
                $equivalence->setCniDateDelivrance(null);
                $equivalence->setCniLieuDelivrance(null);
                $equivalence->setCniDateDuplicata(null);
                $equivalence->setCniLieuDuplicata(null);
            }

            try {
                $em->persist($equivalence);
                $em->flush();
                $logger->info('Dossier créé', ['id' => $equivalence->getId(), 'numero' => $numeroDossier, 'user' => $user?->getUserIdentifier()]);
                $this->addFlash('success', 'Dossier créé avec succès.');
                return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
            } catch (\Exception $e) {
                $logger->error('Création dossier', ['error' => $e->getMessage()]);
                $this->addFlash('danger', 'Une erreur technique est survenue.');
            }
        }

        return $this->render('equivalence/new.html.twig', ['form' => $form->createView()]);
    }

   // src/Controller/EquivalenceController.php

#[Route('/{id}', name: 'equivalence_show', methods: ['GET'])]
#[IsGranted(EquivalenceVoter::VIEW, subject: 'equivalence')]
public function show(Equivalence $equivalence): Response
{
    // Forcer le chargement des relations pour éviter les requêtes N+1
    $this->entityManager->createQueryBuilder()
        ->select('e', 'n', 'd', 'et', 'p', 'doc')
        ->from(Equivalence::class, 'e')
        ->leftJoin('e.nationalite', 'n')
        ->leftJoin('e.diplomeReference', 'd')
        ->leftJoin('d.etablissement', 'et')
        ->leftJoin('et.pays', 'p')
        ->leftJoin('e.documents', 'doc')
        ->where('e.id = :id')
        ->setParameter('id', $equivalence->getId())
        ->getQuery()
        ->getOneOrNullResult();

    return $this->render('equivalence/show.html.twig', [
        'equivalence' => $equivalence,
    ]);
}
 #[Route('/{id}/edit', name: 'equivalence_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
#[IsGranted(EquivalenceVoter::EDIT, subject: 'equivalence')]
public function edit(
    Equivalence $equivalence,  // ← Changement clé : injection directe par le ParamConverter
    Request $request,
    EntityManagerInterface $em,
    LoggerInterface $logger,
    DocumentUploader $uploader
): Response {
    // Vérification du statut (modifiable seulement en brouillon ou soumis)
    if (!in_array($equivalence->getStatus(), [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED
    ])) {
        $this->addFlash('warning', 'Ce dossier ne peut plus être modifié.');
        return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
    }

    // Formulaire
    $form = $this->createForm(EquivalenceType::class, $equivalence);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Validation CNI selon âge
        $dateNaissance = $equivalence->getDateNaissance();
        $age = $dateNaissance ? (new \DateTimeImmutable())->diff($dateNaissance)->y : null;

        if ($age !== null && $age >= 18) {
            if (empty($equivalence->getCni()) || empty($equivalence->getCniDateDelivrance()) || empty($equivalence->getCniLieuDelivrance())) {
                $this->addFlash('danger', 'Pour un majeur, le numéro CNI, la date et le lieu de délivrance sont obligatoires.');
                return $this->render('equivalence/edit.html.twig', [
                    'form' => $form->createView(),
                    'equivalence' => $equivalence
                ]);
            }
        } else {
            // Mineur : effacement des infos CNI
            $equivalence->setCni(null);
            $equivalence->setCniDateDelivrance(null);
            $equivalence->setCniLieuDelivrance(null);
            $equivalence->setCniDateDuplicata(null);
            $equivalence->setCniLieuDuplicata(null);
        }

        // Synchronisation diplôme / établissement (si besoin)
        if ($equivalence->getDiplomeReference()) {
            $diplomeRef = $equivalence->getDiplomeReference();
            $equivalence->setDiplome($diplomeRef->getTitre() ?? '');
            if ($diplomeRef->getEtablissement()) {
                $etab = $diplomeRef->getEtablissement();
                $equivalence->setUniversite($etab->getNom() ?? '');
                $equivalence->setPays($etab->getPays()?->getNomFrFr() ?? '');
            }
        }

        // Gestion des documents (upload, remplacement)
        $documentsMapping = [
            'diplomaFile'    => Document::TYPE_DIPLOME,
            'transcriptFile' => Document::TYPE_RELEVE,
            'identityFile'   => Document::TYPE_CIN,
        ];

        foreach ($documentsMapping as $field => $type) {
            $file = $form->get($field)->getData();
            if (!$file instanceof UploadedFile) {
                continue;
            }

            try {
                // Nouveau document
                $newDocument = new Document();
                $uploader->upload($file, $newDocument, $type);
                $equivalence->addDocument($newDocument);
                $em->persist($newDocument);

                // Suppression des anciens du même type
                foreach ($equivalence->getDocuments() as $existingDocument) {
                    if ($existingDocument === $newDocument) continue;
                    if ($existingDocument->getType() !== $type) continue;

                    $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($existingDocument->getPath(), '/');
                    if (is_file($oldPath)) {
                        try { unlink($oldPath); } catch (\Throwable $e) { /* log */ }
                    }
                    $equivalence->removeDocument($existingDocument);
                    $em->remove($existingDocument);
                }
            } catch (\Throwable $e) {
                $logger->error('Erreur upload document modification', [
                    'dossier_id' => $equivalence->getId(),
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
                $this->addFlash('danger', 'Erreur lors du téléversement du document ' . $type);
                return $this->render('equivalence/edit.html.twig', [
                    'form' => $form->createView(),
                    'equivalence' => $equivalence,
                ]);
            }
        }

        // Sauvegarde finale
        try {
            $em->flush();
            $logger->info('Dossier modifié', ['id' => $equivalence->getId(), 'user' => $this->getUser()?->getUserIdentifier()]);
            $this->addFlash('success', 'Dossier modifié avec succès.');
            return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
        } catch (\Throwable $e) {
            $logger->error('Erreur modification dossier', ['id' => $equivalence->getId(), 'error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur technique lors de la modification.');
        }
    }

    return $this->render('equivalence/edit.html.twig', [
        'form' => $form->createView(),
        'equivalence' => $equivalence,
    ]);
}

  #[Route('/{id}/transition/{transition}', name: 'equivalence_transition', methods: ['POST'])]
    #[IsGranted(EquivalenceVoter::EDIT, subject: 'equivalence')]
    public function transition(Request $request, Equivalence $equivalence, string $transition, #[Autowire(service: 'state_machine.equivalence_state_machine')] WorkflowInterface $workflow, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('transition_' . $equivalence->getId(), $token)) {
        $logger->warning('CSRF invalide', ['dossier_id' => $equivalence->getId(), 'transition' => $transition]);
        $this->addFlash('danger', 'Token CSRF invalide.');
        return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
    }

    $session = $request->getSession();

    if (in_array($transition, self::SENSITIVE_TRANSITIONS, true)) {
        $otpValidated = $session->get('otp_validated_' . $equivalence->getId(), false);
        if (!$otpValidated) {
            $logger->info('OTP requis pour transition sensible', [
                'dossier_id' => $equivalence->getId(),
                'transition' => $transition,
                'user' => $this->getUser()?->getUserIdentifier(),
            ]);
            $session->set('pending_transition_' . $equivalence->getId(), $transition);
            return $this->redirectToRoute('equivalence_confirm_code', ['id' => $equivalence->getId()]);
        }
        $session->remove('otp_validated_' . $equivalence->getId());
    }

    if (!$workflow->can($equivalence, $transition)) {
        $logger->warning('Transition refusée par workflow', [
            'dossier_id' => $equivalence->getId(),
            'transition' => $transition,
            'status' => $equivalence->getStatus(),
            'user' => $this->getUser()?->getUserIdentifier(),
        ]);
        $this->addFlash('danger', sprintf("Transition '%s' non autorisée.", $transition));
        return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
    }

    $oldStatus = $equivalence->getStatus();

    try {
        $workflow->apply($equivalence, $transition);
        match ($transition) {
            'approve' => $equivalence->setDecision(self::DECISION_ACCEPTED)->setClassement(self::CLASSEMENT_A2),
            'reject'  => $equivalence->setDecision(self::DECISION_REJECTED),
            default   => null,
        };
        $em->flush();
        $logger->info('Transition appliquée', [
            'dossier_id' => $equivalence->getId(),
            'transition' => $transition,
            'from' => $oldStatus,
            'to' => $equivalence->getStatus(),
            'user' => $this->getUser()?->getUserIdentifier(),
        ]);
        $this->addFlash('success', sprintf("Transition '%s' effectuée.", $transition));
    } catch (\Throwable $e) {
        $logger->error('Erreur transition workflow', [
            'dossier_id' => $equivalence->getId(),
            'transition' => $transition,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        $this->addFlash('danger', 'Erreur technique lors de la transition.');
    }

    return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
}

     #[Route('/{id}/pdf', name: 'equivalence_pdf', methods: ['GET'])]
    #[IsGranted(EquivalenceVoter::VIEW, subject: 'equivalence')]
    public function pdf(Equivalence $equivalence, PdfGenerator $generator): Response
    {
        return $generator->generateArrete($equivalence);
    }

     // DELETE – protégé par Voter
    #[Route('/{id}/delete', name: 'equivalence_delete', methods: ['POST'])]
    #[IsGranted(EquivalenceVoter::DELETE, subject: 'equivalence')]
    public function delete(Request $request, Equivalence $equivalence, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $equivalence->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('equivalence_index');
        }

        if (!in_array($equivalence->getStatus(), [self::STATUS_DRAFT, self::STATUS_REJECTED])) {
            $this->addFlash('danger', 'Seuls les brouillons ou dossiers rejetés peuvent être supprimés.');
            return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
        }

        try {
            $id = $equivalence->getId();
            $em->remove($equivalence);
            $em->flush();
            $logger->info('Dossier supprimé', ['dossier_id' => $id, 'user' => $this->getUser()?->getUserIdentifier()]);
            $this->addFlash('success', 'Dossier supprimé.');
        } catch (\Exception $e) {
            $logger->error('Erreur suppression', ['error' => $e->getMessage()]);
            $this->addFlash('danger', 'Erreur lors de la suppression.');
        }

        return $this->redirectToRoute('equivalence_index');
    }

    #[Route('/code-request/{id}', name: 'equivalence_request_code', methods: ['POST'])]
    public function requestCode(Equivalence $equivalence, CodeSender $codeSender): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $equivalence);

        $last = $equivalence->getCodeRequestedAt();
        $now = new \DateTimeImmutable();
        if ($last && $now->getTimestamp() - $last->getTimestamp() < 60) {
            return $this->json(['error' => 'Veuillez patienter 60 secondes'], 429);
        }

        try {
            $codeSender->sendCode($equivalence);
            return $this->json(['status' => 'sent']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'envoi'], 500);
        }
    }

#[Route('/confirm-code/{id}', name: 'equivalence_confirm_code', methods: ['GET', 'POST'])]
public function confirmCode(
    Equivalence $equivalence,
    Request $request,
    EntityManagerInterface $em,
    CodeSender $codeSender,
    LoggerInterface $logger,
    #[Autowire(service: 'state_machine.equivalence_state_machine')]
    WorkflowInterface $workflow
): Response {
    $this->denyAccessUnlessGranted('EDIT', $equivalence);
    $session = $request->getSession();

    $pendingTransition = $session->get('pending_transition_' . $equivalence->getId());
    if (!$pendingTransition || !in_array($pendingTransition, self::SENSITIVE_TRANSITIONS, true)) {
        $this->addFlash('warning', 'Aucune action en attente.');
        return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
    }

    if (!$equivalence->getEmail()) {
        $this->addFlash('danger', 'Aucune adresse email associée.');
        return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
    }

    // Envoi automatique OTP si nécessaire
    $now = new \DateTimeImmutable();
    $requestedAt = $equivalence->getCodeRequestedAt();
    $hasCode = $equivalence->getConfirmationCode() !== null;
    $isExpired = $requestedAt && ($now->getTimestamp() - $requestedAt->getTimestamp()) >= 900;
    $codeValid = $hasCode && $requestedAt && !$isExpired;

    if (!$codeValid) {
        try {
            $codeSender->sendCode($equivalence);
            $this->addFlash('info', 'Un code de confirmation a été envoyé à votre adresse email.');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Impossible d’envoyer le code.');
            return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
        }
    } elseif ($hasCode && !$isExpired) {
        $this->addFlash('info', 'Un code a déjà été envoyé. Saisissez-le ci-dessous.');
    }

    // Formulaire OTP
    $form = $this->createForm(ConfirmationCodeType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $submittedCode = $form->get('code')->getData();
        $storedCode = $equivalence->getConfirmationCode();
        $requestedAt = $equivalence->getCodeRequestedAt();
        $isExpired = !$requestedAt || ($now->getTimestamp() - $requestedAt->getTimestamp()) > 900;

        if ($storedCode && !$isExpired && hash_equals($storedCode, $submittedCode)) {
            // Code valide - exécuter la transition immédiatement
            $equivalence->setConfirmationCode(null);
            $equivalence->setCodeRequestedAt(null);

            try {
                $workflow->apply($equivalence, $pendingTransition);

                if ($pendingTransition === 'approve') {
                    $equivalence->setDecision(self::DECISION_ACCEPTED)->setClassement(self::CLASSEMENT_A2);
                } elseif ($pendingTransition === 'reject') {
                    $equivalence->setDecision(self::DECISION_REJECTED);
                }

                $em->flush();

                $logger->info('Transition exécutée après OTP', [
                    'dossier_id' => $equivalence->getId(),
                    'transition' => $pendingTransition,
                ]);

                $session->remove('pending_transition_' . $equivalence->getId());
                $this->addFlash('success', "Transition '$pendingTransition' effectuée.");
                return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
            } catch (\Throwable $e) {
                $logger->error('Erreur transition après OTP', ['error' => $e->getMessage()]);
                $this->addFlash('danger', 'Erreur technique lors de la transition.');
                return $this->redirectToRoute('equivalence_show', ['id' => $equivalence->getId()]);
            }
        }

        $this->addFlash('danger', $isExpired ? 'Code expiré.' : 'Code incorrect.');
    }

    return $this->render('equivalence/confirm_code.html.twig', [
        'form' => $form->createView(),
        'equivalence' => $equivalence,
        'transition' => $pendingTransition,
    ]);
}

#[Route('/test-mail', name: 'test_mail')]
public function testMail(MailerInterface $mailer): Response
{
    $to = 'antara.tombohasina@gmail.com'; // Remplacez par votre adresse de test
    $subject = 'Test d’envoi d’email depuis Symfony';
    $html = '<h1>Test réussi</h1><p>Votre configuration mailer fonctionne correctement.</p>';

    $email = (new Email())
        ->from('system.info.return.28@gmail.com')
        ->to($to)
        ->subject($subject)
        ->html($html);

    try {
        $mailer->send($email);
        $this->addFlash('success', 'Email envoyé avec succès à ' . $to);
    } catch (\Throwable $e) {
        $this->addFlash('danger', 'Erreur lors de l’envoi : ' . $e->getMessage());
    }

    return $this->redirectToRoute('equivalence_index');
}
#[Route('/force-otp/{id}', name: 'force_otp')]
public function forceOtp(Equivalence $equivalence, CodeSender $codeSender): Response
{
    $codeSender->sendCode($equivalence);
    return new Response('Code envoyé à ' . $equivalence->getEmail());
}
}
