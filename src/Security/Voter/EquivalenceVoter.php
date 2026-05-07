<?php

namespace App\Security\Voter;

use App\Entity\Equivalence;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EquivalenceVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::DELETE
        ]) && $subject instanceof Equivalence;
    }

    protected function voteOnAttribute(
        string $attribute,
        $subject,
        TokenInterface $token
    ): bool {

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Equivalence $equivalence */
        $equivalence = $subject;

        // =========================
        // ADMIN
        // =========================
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // =========================
        // COMMISSION
        // =========================
        if (in_array('ROLE_COMMISSION', $user->getRoles())) {
            return true;
        }

        // =========================
        // AGENT
        // =========================
        if (in_array('ROLE_AGENT', $user->getRoles())) {
            return true;
        }

        // =========================
        // CANDIDAT
        // =========================
        if (in_array('ROLE_CANDIDAT', $user->getRoles())) {

            $isOwner =
                $equivalence->getUser() &&
                $equivalence->getUser()->getId() === $user->getId();

            if (!$isOwner) {
                return false;
            }

            return match ($attribute) {

                self::VIEW => true,

                self::EDIT => in_array(
                    $equivalence->getStatus(),
                    [
                        'draft',
                        'submitted',
                        'correction_requested'
                    ]
                ),

                self::DELETE => false,

                default => false,
            };
        }

        // =========================
        // ETABLISSEMENT
        // =========================
        if (in_array('ROLE_ETABLISSEMENT', $user->getRoles())) {
            return false;
        }

        return false;
    }
}
