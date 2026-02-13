<?php

namespace App\Command;

use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:seed-demo',
    description: 'Seed demo data (idempotent) without DoctrineFixturesBundle.'
)]

/**
 * Command to seed demo data for themes, cursus, and lessons.
 */
final class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Idempotence: if themes already exist, skip
        $existingThemes = (int) $this->em->getRepository(Theme::class)->count([]);
        if ($existingThemes > 0) {
            $output->writeln(sprintf('Seed ignorée : thèmes déjà présents (%d).', $existingThemes));
            return Command::SUCCESS;
        }

        $output->writeln('Seed de démo en cours...');

        $now = new \DateTime();

        $dataset = [
            [
                'themeTitle' => 'Musique',
                'cursus' => [
                    [
                        'title' => 'Cursus d\'initiation à la guitare',
                        'price' => 50,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Découverte de l\'instrument', 'videoURL' => 'assets/videos/lecon-musique.mp4', 'price' => 26],
                            ['title' => 'Leçon n°2 : Les accords et les gammes', 'videoURL' => 'assets/videos/lecon-musique.mp4', 'price' => 26],
                        ],
                    ],
                    [
                        'title' => 'Cursus d\'initiation au piano',
                        'price' => 50,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Découverte de l\'instrument', 'videoURL' => 'assets/videos/lecon-musique.mp4', 'price' => 26],
                            ['title' => 'Leçon n°2 : Les accords et les gammes', 'videoURL' => 'assets/videos/lecon-musique.mp4', 'price' => 26],
                        ],
                    ],
                ],
            ],
            [
                'themeTitle' => 'Informatique',
                'cursus' => [
                    [
                        'title' => 'Cursus d\'initiation au développement web',
                        'price' => 60,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Les langages Html et CSS', 'videoURL' => 'assets/videos/lecon-informatique.mp4', 'price' => 32],
                            ['title' => 'Leçon n°2 : Dynamiser votre site avec Javascript', 'videoURL' => 'assets/videos/lecon-informatique.mp4', 'price' => 32],
                        ],
                    ],
                ],
            ],
            [
                'themeTitle' => 'Jardinage',
                'cursus' => [
                    [
                        'title' => 'Cursus d\'initiation au jardinage',
                        'price' => 30,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Les outils du jardinier', 'videoURL' => 'assets/videos/lecon-jardinage.mp4', 'price' => 16],
                            ['title' => 'Leçon n°2 : Jardiner avec la lune', 'videoURL' => 'assets/videos/lecon-jardinage.mp4', 'price' => 16],
                        ],
                    ],
                ],
            ],
            [
                'themeTitle' => 'Cuisine',
                'cursus' => [
                    [
                        'title' => 'Cursus d\'initiation à la cuisine',
                        'price' => 44,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Les modes de cuisson', 'videoURL' => 'assets/videos/lecon-cuisine.mp4', 'price' => 23],
                            ['title' => 'Leçon n°2 : Les saveurs', 'videoURL' => 'assets/videos/lecon-cuisine.mp4', 'price' => 23],
                        ],
                    ],
                    [
                        'title' => 'Cursus d\'initiation à l\'art du dressage culinaire',
                        'price' => 48,
                        'lessons' => [
                            ['title' => 'Leçon n°1 : Mettre en oeuvre le style dans l\'assiette', 'videoURL' => 'assets/videos/lecon-cuisine.mp4', 'price' => 26],
                            ['title' => 'Leçon n°2 : Harmoniser un repas à quatre plats', 'videoURL' => 'assets/videos/lecon-cuisine.mp4', 'price' => 26],
                        ],
                    ],
                ],
            ],
        ];

        $loremDescription = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. "
            . "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. "
            . "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";

        $loremContent = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. "
            . "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n\n"
            . "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. "
            . "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. "
            . "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

        foreach ($dataset as $themeData) {
            $theme = new Theme();
            $theme->setTitle($themeData['themeTitle']);
            $theme->setDescription($loremDescription);

            $slug = $this->slugger->slug($themeData['themeTitle'])->lower()->toString();
            $theme->setSlug($slug);

            $theme->setCreatedAt(clone $now);
            $theme->setUpdatedAt(clone $now);

            $this->em->persist($theme);

            foreach ($themeData['cursus'] as $cursusData) {
                $cursus = new Cursus();
                $cursus->setTheme($theme);
                $cursus->setTitle($cursusData['title']);
                $cursus->setDescription($loremDescription);
                $cursus->setPrice($cursusData['price']);
                $cursus->setIsActive(true);

                $cursus->setCreatedAt(clone $now);
                $cursus->setUpdatedAt(clone $now);

                $this->em->persist($cursus);

                $position = 1;
                foreach ($cursusData['lessons'] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setCursus($cursus);
                    $lesson->setTitle($lessonData['title']);
                    $lesson->setContent($loremContent);
                    $lesson->setVideoUrl($lessonData['videoURL']);
                    $lesson->setPosition($position);
                    $lesson->setPrice($lessonData['price']);
                    $lesson->setIsActive(true);

                    $lesson->setCreatedAt(clone $now);
                    $lesson->setUpdatedAt(clone $now);

                    $this->em->persist($lesson);
                    $position++;
                }
            }
        }

        $this->em->flush();

        $output->writeln('Seed de démo terminé.');
        return Command::SUCCESS;
    }
}
