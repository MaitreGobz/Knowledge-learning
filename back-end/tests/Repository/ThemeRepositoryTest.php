<?php

namespace App\Tests\Repository;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test for the ThemeRepository.
 */
final class ThemeRepositoryTest extends KernelTestCase
{
    private ThemeRepository $repo;
    /**
     * Setup before each test case.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->repo = static::getContainer()->get(ThemeRepository::class);
    }

    /**
     * Test findAllWithCursusPreview() returns themes with expected structure.
     */
    public function testFindAllWithCursusPreviewReturnsThemes(): void
    {
        $themes = $this->repo->findAllWithCursusPreview();

        self::assertIsArray($themes);
        self::assertNotEmpty($themes, 'La base de test doit contenir des thèmes (fixtures attendues).');

        // Type + access to properties
        self::assertInstanceOf(Theme::class, $themes[0]);
        self::assertNotEmpty($themes[0]->getTitle());
    }

    /**
     * Test that the returned themes have their cursus collection accessible.
     */
    public function testFindAllWithCursusPreviewAllowsAccessToCursusCollection(): void
    {
        $themes = $this->repo->findAllWithCursusPreview();

        self::assertNotEmpty($themes, 'Fixtures attendues : aucun thème retourné.');

        $foundAtLeastOneCursus = false;

        foreach ($themes as $theme) {
            $cursusCollection = $theme->getCursus();

            // Check that it is iterable
            self::assertIsIterable($cursusCollection);

            // If there is at least one cursus, check its properties
            if (count($cursusCollection) > 0) {
                $foundAtLeastOneCursus = true;

                $firstCursus = $cursusCollection->first();
                // Doctrine Collection can return false if empty
                self::assertNotFalse($firstCursus);

                self::assertNotNull($firstCursus->getId());
                self::assertNotEmpty($firstCursus->getTitle());

                // We can stop as soon as we have validated one case
                break;
            }
        }
    }
}
