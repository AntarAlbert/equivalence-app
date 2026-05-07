<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    */
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        // Statistiques rapides pour le dashboard
        $totalUsers = $userRepository->count([]);
        $totalAgents = $userRepository->count(['roles' => 'ROLE_AGENT']);
        $totalCommission = $userRepository->count(['roles' => 'ROLE_COMMISSION']);
        $totalAdmins = $userRepository->count(['roles' => 'ROLE_ADMIN']);

        // Derniers utilisateurs inscrits (limite 5)
        $latestUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalAgents' => $totalAgents,
            'totalCommission' => $totalCommission,
            'totalAdmins' => $totalAdmins,
            'latestUsers' => $latestUsers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILISATEURS (CRUD)
    |--------------------------------------------------------------------------
    */
#[Route('/users', name: 'admin_users', methods: ['GET'])]
public function users(
    UserRepository $userRepository,
    PaginatorInterface $paginator,
    Request $request
): Response {
    $page = $request->query->getInt('page', 1);
    $limit = 20;
    $queryBuilder = $userRepository->createQueryBuilder('u')
        ->orderBy('u.id', 'DESC');
    $pagination = $paginator->paginate($queryBuilder, $page, $limit);

    return $this->render('admin/users.html.twig', [
        'pagination' => $pagination,
    ]);
}

    #[Route('/user/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $roles = $request->request->all('roles'); // array

            if (!$email || !$plainPassword) {
                $this->addFlash('danger', 'Email et mot de passe requis.');
                return $this->redirectToRoute('admin_user_new');
            }

            $user = new User();
            $user->setEmail($email);
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles($roles);

            $em->persist($user);
            $em->flush();

            $logger->info('Nouvel utilisateur créé par admin', [
                'admin' => $this->getUser()->getUserIdentifier(),
                'new_user' => $email,
            ]);
            $this->addFlash('success', "Utilisateur $email créé.");

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'user' => null,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $roles = $request->request->all('roles');

            if ($email) {
                $user->setEmail($email);
            }
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            if (!empty($roles)) {
                $user->setRoles($roles);
            }

            $em->flush();
            $logger->info('Utilisateur modifié par admin', [
                'admin' => $this->getUser()->getUserIdentifier(),
                'edited_user' => $user->getUserIdentifier(),
            ]);
            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'user' => $user,
        ]);
    }

   #[Route('/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
public function deleteUser(
    int $id,  // on récupère l'id directement
    Request $request,
    UserRepository $userRepository,
    EntityManagerInterface $em,
    LoggerInterface $logger
): Response {
    $user = $userRepository->find($id);
    if (!$user) {
        $this->addFlash('danger', 'Utilisateur introuvable.');
        return $this->redirectToRoute('admin_users');
    }

    if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
        $this->addFlash('danger', 'Token CSRF invalide.');
        return $this->redirectToRoute('admin_users');
    }
    $currentUser = $this->getUser();

    if ($currentUser instanceof User && $user->getId() === $currentUser->getId()) {
    // if ($user->getId() === $this->getUser()->getId()) {
        $this->addFlash('danger', 'Vous ne pouvez pas vous supprimer vous-même.');
        return $this->redirectToRoute('admin_users');
    }

    $email = $user->getUserIdentifier();
    $em->remove($user);
    $em->flush();

    $logger->warning('Utilisateur supprimé par admin', [
        'admin' => $this->getUser()->getUserIdentifier(),
        'deleted_user' => $email,
    ]);
    $this->addFlash('success', "Utilisateur $email supprimé.");

    return $this->redirectToRoute('admin_users');
}
    /*
    |--------------------------------------------------------------------------
    | JOURNAL SYSTEME
    |--------------------------------------------------------------------------
    */
    #[Route('/logs', name: 'admin_logs')]
    public function logs(Request $request): Response
    {
        // Lecture du fichier de log (var/log/dev.log ou prod.log)
        $logFile = $this->getParameter('kernel.logs_dir') . '/dev.log';
        if ('prod' === $this->getParameter('kernel.environment')) {
            $logFile = $this->getParameter('kernel.logs_dir') . '/prod.log';
        }

        $lines = [];
        if (file_exists($logFile)) {
            $fileContent = file_get_contents($logFile);
            $lines = array_reverse(explode("\n", $fileContent)); // dernières lignes d'abord
            $lines = array_slice($lines, 0, 1000); // limiter à 1000 lignes
        }

        return $this->render('admin/logs.html.twig', [
            'logs' => $lines,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATISTIQUES
    |--------------------------------------------------------------------------
    */
    #[Route('/statistics', name: 'admin_statistics')]
    public function statistics(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        // Nombre d'utilisateurs par rôle
        $stats = [
            'users' => $userRepository->count([]),
            'agents' => $userRepository->count(['roles' => 'ROLE_AGENT']),
            'commission' => $userRepository->count(['roles' => 'ROLE_COMMISSION']),
            'admins' => $userRepository->count(['roles' => 'ROLE_ADMIN']),
        ];

        // Vous pouvez ajouter des stats sur les équivalences via EquivalenceRepository
        // Exemple : $equivalenceRepo->countByStatus()

        return $this->render('admin/statistics.html.twig', [
            'stats' => $stats,
        ]);
    }
}
