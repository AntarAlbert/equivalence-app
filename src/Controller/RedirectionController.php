<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RedirectionController extends AbstractController
{
    #[Route('/after-login', name: 'after_login')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function afterLogin(): Response
    {
        $user = $this->getUser();

        // Sécurité : si l'utilisateur n'est pas authentifié (normalement ne devrait pas arriver)
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $roles = $user->getRoles();

        // Redirection selon le rôle le plus élevé
        if (in_array('ROLE_SUPER_ADMIN', $roles, true) || in_array('ROLE_ADMIN', $roles, true)) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if (in_array('ROLE_COMMISSION', $roles, true)) {
            return $this->redirectToRoute('commission_dashboard');
        }

        if (in_array('ROLE_AGENT', $roles, true)) {
            return $this->redirectToRoute('equivalence_index');
        }

        if (in_array('ROLE_ETABLISSEMENT', $roles, true)) {
            // Route du tableau de bord établissement (à adapter selon vos routes)
            return $this->redirectToRoute('etablissement_diplome_index');
        }

        // Par défaut (ROLE_CANDIDAT ou autres)
        return $this->redirectToRoute('equivalence_index');
    }
}
