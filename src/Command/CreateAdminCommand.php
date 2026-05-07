<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un administrateur'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {

        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $existingUser =
            $this->em
                ->getRepository(User::class)
                ->findOneBy([
                    'email' =>
                        'admin@equivalence.mg'
                ]);

        if ($existingUser) {

            $output->writeln(
                '<error>Utilisateur déjà existant.</error>'
            );

            return Command::FAILURE;
        }

        $user = new User();

        $user->setEmail(
            'admin@equivalence.mg'
        );

        $user->setRoles([
            'ROLE_ADMIN'
        ]);

        $user->setPassword(

            $this->hasher->hashPassword(
                $user,
                'admin123'
            )
        );

        $this->em->persist($user);

        $this->em->flush();

        $output->writeln(
            '<info>Administrateur créé avec succès.</info>'
        );

        return Command::SUCCESS;
    }
}