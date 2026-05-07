<?php

namespace App\Security\Voter;

use App\Entity\Diplome;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DiplomeVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const APPROVE = 'APPROVE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::APPROVE
        ]) && $subject instanceof Diplome;
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

        /** @var Diplome $diplome */
        $diplome = $subject;

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

            return match ($attribute) {

                self::VIEW => true,

                self::EDIT => true,

                self::APPROVE => true,

                default => false,
            };
        }

        // =========================
        // ETABLISSEMENT
        // =========================
        if (in_array('ROLE_ETABLISSEMENT', $user->getRoles())) {

            $isOwner =
                $diplome->getProposedBy() &&
                $diplome->getProposedBy()->getId() === $user->getId();

            if (!$isOwner) {
                return false;
            }

            return match ($attribute) {

                self::VIEW => true,

                self::EDIT =>
                    $diplome->getValidationStatus()
                    === Diplome::STATUS_PENDING,

                self::APPROVE => false,

                default => false,
            };
        }

        // =========================
        // AGENT
        // =========================
        if (in_array('ROLE_AGENT', $user->getRoles())) {

            return
                $attribute === self::VIEW
                &&
                $diplome->getValidationStatus()
                === Diplome::STATUS_APPROVED;
        }

        // =========================
        // CANDIDAT
        // =========================
        if (in_array('ROLE_CANDIDAT', $user->getRoles())) {
            return false;
        }

        return false;
    }
}
