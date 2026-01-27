<?php

namespace App\Security;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\AccessRightRepository;

/**
 * Class to check if a user has access rights to lessons or cursus.
 */
final class AccessRightChecker
{
    public const VALID_PURCHASE_STATUSES = ['paid', 'no_payment_required'];

    public function __construct(
        private readonly AccessRightRepository $accessRightRepository
    ) {}

    // Check if a user can access a specific lesson
    public function canAccessLesson(User $user, Lesson $lesson): bool
    {
        // Direct access via lesson purchase
        if ($this->accessRightRepository->lessonAccess($user, $lesson, self::VALID_PURCHASE_STATUSES)) {
            return true;
        }

        // Indirect access via cursus purchase
        $cursus = $lesson->getCursus();
        if ($cursus === null) {
            return false;
        }

        return $this->accessRightRepository->cursusAccess($user, $cursus, self::VALID_PURCHASE_STATUSES);
    }
}
