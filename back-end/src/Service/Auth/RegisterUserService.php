<?php

namespace App\Service\Auth;

use App\Dto\Auth\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
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

        //Token 
        $token = bin2hex(random_bytes(32));
        $user->setToken($token);
        $user->setTokenExpiresAt(new \DateTime('+12 hours'));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
