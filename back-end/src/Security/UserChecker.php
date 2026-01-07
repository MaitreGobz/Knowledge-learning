<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // Only check User instances
        if (!$user instanceof User) {
            return;
        }

        // Check if the account is active and if the email is verified
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Compte désactivé');
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Email non vérifié');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void {}
}
