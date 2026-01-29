<?php

namespace App\Service\Private;

use App\Entity\CursusValidation;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Certification;
use App\Repository\LessonRepository;
use App\Repository\LessonValidationRepository;
use App\Repository\CursusValidationRepository;
use App\Repository\CertificationRepository;
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
        private readonly CertificationRepository $certificationRepository,
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
        $totalLessons = $this->lessonRepository->countActiveByCursusId($cursus->getId());
        $validatedCount = $this->lessonValidationRepository->validatedLessonInCursusByUser($user, $cursus->getId());

        $cursusValidated = false;

        if ($totalLessons > 0 && $validatedCount === $totalLessons) {
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

                $theme = $cursus->getTheme();
                $this->createThemeCertification($user, $theme);
            }
        }

        return [
            'lessonValidated' => true,
            'cursusValidated' => $cursusValidated,
            'cursusId' => $cursus->getId(),
        ];
    }

    /**
     * Create a certification for the user
     */
    private function createThemeCertification(User $user, ?Theme $theme): bool
    {
        // If theme is null, cannot create certification
        if ($theme === null) {
            return false;
        }

        // Check if certification already exists
        $existingCert = $this->certificationRepository->findOneBy([
            'user' => $user,
            'theme' => $theme,
        ]);

        // If certification exists, do not create a new one
        if ($existingCert) {
            return false;
        }

        // Check if all lessons in the theme are validated
        $totalLessonsInTheme = $this->lessonRepository->countActiveByThemeId($theme->getId());
        if ($totalLessonsInTheme <= 0) {
            return false;
        }

        // Count validated lessons in the theme by the user
        $validatedLessonInTheme = $this->lessonValidationRepository->validatedLessonsInThemeByUser($user, $theme->getId());

        // If not all lessons are validated, do not create certification
        if ($validatedLessonInTheme !== $totalLessonsInTheme) {
            return false;
        }

        // Create new Certification
        $cert = new Certification();
        $cert->setUser($user);
        $cert->setTheme($theme);
        $cert->setValidatedAt(new \DateTime());

        $this->em->persist($cert);
        $this->em->flush();

        return true;
    }
}
