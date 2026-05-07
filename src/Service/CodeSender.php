<?php
namespace App\Service;

use App\Entity\Equivalence;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CodeSender
{
public function __construct(
    private MailerInterface $mailer,
    private EntityManagerInterface $em,
    private string $adminEmail,
    private LoggerInterface $logger
) {}

public function sendCode(Equivalence $equivalence): void
{
    $this->logger->info('Envoi OTP déclenché', ['dossier_id' => $equivalence->getId()]);

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $equivalence->setConfirmationCode($code);
    $equivalence->setCodeRequestedAt(new \DateTimeImmutable());
    $this->em->flush();

    $to = $equivalence->getEmail();
    if (!$to) {
        $this->logger->error('Aucun email associé au dossier', ['dossier_id' => $equivalence->getId()]);
        throw new \Exception("Aucun email associé au dossier.");
    }

    $this->logger->info('Tentative d’envoi vers ' . $to);

    $email = (new Email())
        ->from($this->adminEmail)
        ->to($to)
        ->subject('Code de confirmation - Équivalence')
        ->html("<p>Votre code de confirmation est : <strong>$code</strong></p>");

    $this->mailer->send($email);
    $this->logger->info('Email OTP envoyé avec succès');
}
}
