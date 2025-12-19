<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly SluggerInterface $slugger) {}
    public function load(ObjectManager $manager): void
    {
        /**
         * Dataset based on the topic (Beta version)
         * Structure:
         * - Theme
         *      - Cursus
         *          - Lesson
         */
        $now = new \Datetime();
        $dataset = [
            [
                'themeTitle' => 'Musique',
                'cursus' => [
                    [
                        'title' => 'Cursus d\'initiation à la guitare',
                        'price' => 50,
                        'lessons' => [
                            [
                                'title' => 'Leçon n°1 : Découverte de l\'instrument',
                                'videoURL' => '/videos/leçon-musique.mp4',
                                'price' => 26
                            ],
                            [
                                'title' => 'Leçon n°2 : Les accords et les gammes',
                                'videoURL' => '/videos/leçon-musique.mp4',
                                'price' => 26
                            ],
                        ],
                    ],
                    [
                        'title' => 'Cursus d\'initiation au piano',
                        'price' => 50,
                        'lessons' => [
                            [
                                'title' => 'Leçon n°1 : Découverte de l\'instrument',
                                'videoURL' => '/videos/leçon-musique.mp4',
                                'price' => 26
                            ],
                            [
                                'title' => 'Leçon n°2 : Les accords et les gammes',
                                'videoURL' => '/videos/leçon-musique.mp4',
                                'price' => 26
                            ],
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
                            [
                                'title' => 'Leçon n°1 : Les langages Html et CSS',
                                'videoURL' => '/videos/leçon-informatique.mp4',
                                'price' => 32,
                            ],
                            [
                                'title' => 'Leçon n°2 : Dynamiser votre site avec Javascript',
                                'videoURL' => '/videos/leçon-informatique.mp4',
                                'price' => 32,
                            ],
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
                            [
                                'title' => 'Leçon n°1 : Les outils du jardinier',
                                'videoURL' => '/videos/leçon-jardinage.mp4',
                                'price' => 16,
                            ],
                            [
                                'title' => 'Leçon n°2 : Jardiner avec la lune',
                                'videoURL' => '/videos/leçon-jardinage.mp4',
                                'price' => 16,
                            ],
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
                            [
                                'title' => 'Leçon n°1 : Les modes de cuisson',
                                'videoURL' => '/videos/leçon-cuisine.mp4',
                                'price' => 23,
                            ],
                            [
                                'title' => 'Leçon n°2 : Les saveurs',
                                'videoURL' => '/videos/leçon-cuisine.mp4',
                                'price' => 23,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Cursus d\'initiation à l\'art du dressage culinaire',
                        'price' => 48,
                        'lessons' => [
                            [
                                'title' => 'Leçon n°1 : Mettre en oeuvre le style dans l\'assiette',
                                'videoURL' => '/videos/leçon-cuisine.mp4',
                                'price' => 26,
                            ],
                            [
                                'title' => 'Leçon n°2 : Harmoniser un repas à quatre plats',
                                'videoURL' => '/videos/leçon-cuisine.mp4',
                                'price' => 26,
                            ],
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
            // Theme (slug is required + unique)
            $theme = new Theme();
            $theme->setTitle($themeData['themeTitle']);
            $theme->setDescription($loremDescription);

            $slug = $this->slugger->slug($themeData['themeTitle'])->lower()->toString();
            $theme->setSlug($slug);

            $theme->setCreatedAt(clone $now);
            $theme->setUpdatedAt(clone $now);

            $manager->persist($theme);

            //Cursus
            foreach ($themeData['cursus'] as $cursusData) {
                $cursus = new Cursus();
                $cursus->setTheme($theme);
                $cursus->setTitle($cursusData['title']);
                $cursus->setDescription($loremDescription);
                $cursus->setPrice($cursusData['price']);
                $cursus->setIsActive(true);

                $cursus->setCreatedAt(clone $now);
                $cursus->setUpdatedAt(clone $now);

                $manager->persist($cursus);
                $position = 1;

                foreach ($cursusData['lessons'] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setCursus($cursus);
                    $lesson->setTitle($lessonData['title']);
                    $lesson->setContent($loremContent);

                    $lesson->setVideoUrl($lessonData['videoURL']);

                    $lesson->setPosition($position);
                    $lesson->setPrice($lessonData['price']);

                    // Force active to true (as requested).
                    $lesson->setIsActive(true);

                    $lesson->setCreatedAt(clone $now);
                    $lesson->setUpdatedAt(clone $now);

                    $manager->persist($lesson);
                    $position++;
                }
            }
        }

        $manager->flush();
    }
}
