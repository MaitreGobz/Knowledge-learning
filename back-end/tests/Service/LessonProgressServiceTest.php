<?php

namespace App\Tests\Service;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\CursusValidationRepository;
use App\Service\Private\LessonProgressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LessonProgressServiceTest extends KernelTestCase
{
    public function testCursusValidated(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // Get services and repositories
        $em = $container->get(EntityManagerInterface::class);
        $service = $container->get(LessonProgressService::class);
        $cursusValidationRepo = $container->get(CursusValidationRepository::class);

        // Create test data
        $theme = (new Theme())
            ->setTitle('Theme Test')
            ->setDescription('Description Test')
            ->setSlug('theme-test');

        $cursus = (new Cursus())
            ->setTitle('Cursus Test')
            ->setDescription('Cursus Description Test')
            ->setPrice(25)
            ->setIsActive(true)
            ->setTheme($theme);

        $lesson1 = (new Lesson())
            ->setTitle('Lesson 1')
            ->setContent('Content 1')
            ->setPrice(25)
            ->setPosition(1)
            ->setIsActive(true)
            ->setCursus($cursus);
        $lesson2 = (new Lesson())
            ->setTitle('Lesson 2')
            ->setContent('Content 2')
            ->setPrice(25)
            ->setPosition(2)
            ->setIsActive(true)
            ->setCursus($cursus);

        $user = (new User())
            ->setEmail('test_' . uniqid() . '@example.com')
            ->setIsActive(true)
            ->setIsVerified(true)
            ->setRoles(['ROLE_USER'])
            ->setPassword('dummy');

        // Persist test data
        $em->persist($theme);
        $em->persist($cursus);
        $em->persist($lesson1);
        $em->persist($lesson2);
        $em->persist($user);
        $em->flush();

        // Validate first lesson
        $result1 = $service->validateLesson($user, $lesson1);

        // Check that cursus is not yet validated
        self::assertFalse($result1['cursusValidated']);
        self::assertNull(
            $cursusValidationRepo->findOneBy(['user' => $user, 'cursus' => $cursus])
        );

        // Validate second lesson
        $result2 = $service->validateLesson($user, $lesson2);

        // Cursus validated
        self::assertTrue($result2['cursusValidated']);
        self::assertNotNull(
            $cursusValidationRepo->findOneBy(['user' => $user, 'cursus' => $cursus])
        );
    }
}
