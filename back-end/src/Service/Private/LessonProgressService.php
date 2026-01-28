<?php

namespace App\Service\Private;

use App\Entity\CursusValidation;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use App\Repository\LessonRepository;
use App\Repository\LessonValidationRepository;
use App\Repository\CursusValidationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to manage lesson progress and validations.
 */
final class LessonProgressService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LessonRepository $lessonRepository,
        private readonly LessonValidationRepository $lessonValidationRepository,
        private readonly CursusValidationRepository $cursusValidationRepository,
    ) {}

    public function validateLesson(User $user, Lesson $lesson): array
    {
        // Check if the lesson is already validated
        $existing = $this->lessonValidationRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
        ]);

        if ($existing) {
            throw new \LogicException('LESSON_ALREADY_VALIDATED');
        }

        // Create new LessonValidation
        $lv = new LessonValidation();
        $lv->setUser($user);
        $lv->setLesson($lesson);
        $lv->setValidatedAt(new \DateTime());

        $this->em->persist($lv);
        $this->em->flush();

        // Check if the entire cursus is validated
        $cursus = $lesson->getCursus();
        $totalLessons = $this->lessonRepository->count(['cursus' => $cursus]);
        $validatedCount = $this->lessonValidationRepository->validatedLessonInCursusByUser($user, $cursus->getId());

        $cursusValidated = false;

        if ($totalLessons > 0 && $validatedCount >= $totalLessons) {
            $cvExisting = $this->cursusValidationRepository->findOneBy([
                'user' => $user,
                'cursus' => $cursus,
            ]);

            if (!$cvExisting) {
                $cv = new CursusValidation();
                $cv->setUser($user);
                $cv->setCursus($cursus);
                $cv->setValidatedAt(new \DateTime());

                $this->em->persist($cv);
                $this->em->flush();

                $cursusValidated = true;
            }
        }

        return [
            'lessonValidated' => true,
            'cursusValidated' => $cursusValidated,
            'cursusId' => $cursus->getId(),
        ];
    }
}
