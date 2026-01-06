<?php

namespace App\Service\Auth;

use App\Dto\Auth\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailVerifier $emailVerifier,
    ) {}

    public function register(RegisterRequest $dto): User
    {
        // Email uniqueness
        if ($this->userRepository->findOneBy(['email' => $dto->email])) {
            throw new \DomainException("Email déjà existant.");
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);
        $user->setIsVerified(false);

        //Hash password
        $hash = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hash);

        $this->em->persist($user);
        $this->em->flush();

        // Send signed verification email (Mailtrap will receive it)
        $email = (new TemplatedEmail())
            ->from('no-reply@knowledge-learning.local')
            ->to($user->getEmail())
            ->subject('Confirm your email')
            ->htmlTemplate('/registration/verify_email.html.twig')
            ->context([
                'userEmail' => $user->getEmail(),
                'frontendUrl' => $_ENV['FRONTEND_URL'] ?? 'http://127.0.0.1:4200',
            ]);

        $this->emailVerifier->sendEmailConfirmation('api_auth_verify_email', $user, $email);

        return $user;
    }
}
