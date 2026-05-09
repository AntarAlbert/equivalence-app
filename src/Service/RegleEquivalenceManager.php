<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RegleEquivalence;
use App\Repository\RegleEquivalenceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class RegleEquivalenceManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RegleEquivalenceRepository $repository,
    ) {
    }

    public function save(RegleEquivalence $regle): void
    {
        if ($this->hasOverlapWithAnyRule($regle)) {
            throw new \LogicException('Cette période chevauche une autre règle pour ce diplôme.');
        }

        $this->disableOverlappingRules($regle);
        $regle->setActif(true);
        $this->entityManager->persist($regle);
        $this->entityManager->flush();
    }

   private function hasOverlapWithAnyRule(RegleEquivalence $rule): bool
{
    $diplome = $rule->getDiplome();
    $allRules = $this->repository->findBy(['diplome' => $diplome]);

    foreach ($allRules as $existing) {
        if ($existing->getId() === $rule->getId()) {
            continue;
        }
        if ($this->overlaps(
            $rule->getDateDebut(),
            $rule->getDateFin(),
            $existing->getDateDebut(),
            $existing->getDateFin()
        )) {
            return true;
        }
    }
    return false;
}

  private function disableOverlappingRules(RegleEquivalence $newRule): void
{
    $diplome = $newRule->getDiplome();
    if (!$diplome) {
        return;
    }

    $newStart = $newRule->getDateDebut();
    $newEnd   = $newRule->getDateFin();

    $existingRules = $this->repository->findActiveRulesForDiplome($diplome);

    foreach ($existingRules as $existingRule) {
        if ($existingRule->getId() === $newRule->getId()) {
            continue;
        }

        $existingStart = $existingRule->getDateDebut();
        $existingEnd   = $existingRule->getDateFin();

        // Vérifier chevauchement
        if ($this->overlaps($existingStart, $existingEnd, $newStart, $newEnd)) {
            $existingRule->setActif(false);
        }
    }
}

private function overlaps(
    ?\DateTimeImmutable $start1,
    ?\DateTimeImmutable $end1,
    ?\DateTimeImmutable $start2,
    ?\DateTimeImmutable $end2
): bool {
    // Une règle sans date de fin est considérée comme valide indéfiniment
    $end1 = $end1 ?? new \DateTimeImmutable('9999-12-31');
    $end2 = $end2 ?? new \DateTimeImmutable('9999-12-31');

    return $start1 <= $end2 && $start2 <= $end1;
}
// src/Service/RegleEquivalenceManager.php

private function disablePreviousRules(RegleEquivalence $regle): void
{
    $diplome = $regle->getDiplome();
    if (!$diplome) return;

    $existingRules = $this->repository->findActiveRulesForDiplome($diplome);

    foreach ($existingRules as $existingRule) {
        if ($existingRule->getId() !== $regle->getId()) {
            $existingRule->setActif(false);
        }
    }
}
}
