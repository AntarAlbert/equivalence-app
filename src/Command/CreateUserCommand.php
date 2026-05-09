<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un utilisateur avec un rôle spécifique'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse email de l’utilisateur')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe en clair')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Rôle : candidat, etablissement, commission, agent, admin')
            ->addOption('fullname', null, InputOption::VALUE_OPTIONAL, 'Nom complet (optionnel)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getOption('email');
        $plainPassword = $input->getOption('password');
        $role = $input->getOption('role');
        $fullname = $input->getOption('fullname');

        // Validation des entrées
        if (!$email || !$plainPassword || !$role) {
            $output->writeln('<error>Les options --email, --password et --role sont obligatoires.</error>');
            return Command::FAILURE;
        }

        // Mapping des rôles
        $roleMap = [
            'candidat' => ['ROLE_CANDIDAT'],
            'etablissement' => ['ROLE_ETABLISSEMENT', 'ROLE_CANDIDAT'],
            'commission' => ['ROLE_COMMISSION', 'ROLE_AGENT', 'ROLE_CANDIDAT'],
            'agent' => ['ROLE_AGENT', 'ROLE_CANDIDAT'],
            'admin' => ['ROLE_ADMIN', 'ROLE_COMMISSION', 'ROLE_AGENT', 'ROLE_ETABLISSEMENT', 'ROLE_CANDIDAT'],
        ];

        $roleKey = strtolower($role);
        if (!isset($roleMap[$roleKey])) {
            $output->writeln('<error>Rôle invalide. Choisissez parmi : candidat, etablissement, commission, agent, admin</error>');
            return Command::FAILURE;
        }

        // Vérifier si l'email existe déjà
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $output->writeln('<error>Un utilisateur avec cet email existe déjà.</error>');
            return Command::FAILURE;
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roleMap[$roleKey]);
        $user->setFullName($fullname);
        $user->setIsVerified(true); // Pour les utilisateurs créés en CLI (ou false selon besoin)
        $user->setVerifiedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $output->writeln('<error>' . $error->getMessage() . '</error>');
            }
            return Command::FAILURE;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Utilisateur créé avec succès.</info>');
        $output->writeln(sprintf('Email : %s', $email));
        $output->writeln(sprintf('Rôles : %s', implode(', ', $user->getRoles())));
        if ($fullname) {
            $output->writeln(sprintf('Nom complet : %s', $fullname));
        }

        return Command::SUCCESS;
    }
}
