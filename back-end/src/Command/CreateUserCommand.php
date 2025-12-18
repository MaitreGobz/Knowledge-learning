<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add a user with a role to the application',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email (unique)')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password (will be hashed)')
            ->addArgument('role', InputArgument::REQUIRED, 'ROLE_USER or ROLE_ADMIN')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $plainPassword = (string) $input->getArgument('password');
        $role = strtoupper((string) $input->getArgument('role'));

        // Validate role
        $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        if (!in_array($role, $allowedRoles, true)) {
            $io->error(sprintf('Invalid role "%s". Allowed roles: %s', $role, implode(', ', $allowedRoles)));
            return Command::INVALID;
        }

        // Check if user with the same email already exists
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing !== null) {
            $io->error(sprintf('A user with email "%s" already exists.', $email));
            return Command::FAILURE;
        }

        //Create user
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);

        // If your User entity has an isActive flag, enable it
        if (method_exists($user, 'setIsActive')) {
            $user->setIsActive(true);
        } elseif (method_exists($user, 'setActive')) {
            $user->setActive(true);
        }

        // isVerified
        if (method_exists($user, 'setIsVerified')) {
            $user->setIsVerified(true);
        } elseif (method_exists($user, 'setVerified')) {
            $user->setVerified(true);
        }

        // created_at / updated_at
        $now = new \DateTime();

        if (method_exists($user, 'setCreatedAt')) {
            $user->setCreatedAt($now);
        }
        if (method_exists($user, 'setUpdatedAt')) {
            $user->setUpdatedAt($now);
        }

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User "%s" with role "%s" has been created.', $email, $role));
        return Command::SUCCESS;
    }
}
