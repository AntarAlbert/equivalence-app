<?php
// src/Security/Voter/EquivalenceVoter.php

namespace App\Security\Voter;

use App\Entity\Equivalence;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EquivalenceVoter extends Voter
{
    public const VIEW = 'EQUIVALENCE_VIEW';
    public const EDIT = 'EQUIVALENCE_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Equivalence;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Admin, agent, commission can see/edit everything
        if (
            in_array('ROLE_AGENT', $user->getRoles())
            || in_array('ROLE_COMMISSION', $user->getRoles())
            || in_array('ROLE_ADMIN', $user->getRoles())
        ) {
            return true;
        }

        /** @var Equivalence $equivalence */
        $equivalence = $subject;

        // Candidat can only view/edit their own dossier
        return $equivalence->getUser() === $user;
    }
}
