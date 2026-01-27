<?php

namespace App\Security\Voter;

use App\Entity\Lesson;
use App\Entity\User;
use App\Security\AccessRightChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LessonVoter extends Voter
{
    public const VIEW = 'LESSON_VIEW';

    public function __construct(private readonly AccessRightChecker $checker) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Lesson;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Admin bypass
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $lesson = $subject;

        return $this->checker->canAccessLesson($user, $lesson);
    }
}
