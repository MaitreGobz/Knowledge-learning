<?php

namespace App\Controller\Api\Private;

use App\Entity\User;
use App\Repository\LessonRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MyLessonsController extends AbstractController
{
    #[Route('/api/private/my-lessons', name: 'api_private_my_lessons', methods: ['GET'])]
    #[OA\Get(
        path: '/api/private/my-lessons',
        summary: 'Liste les leçons accessibles pour l\'utilisateur connecté',
        description: 'Retourne les leçons auxquelles l\'utilisateur a accès via AccessRight (achat leçon ou achat cursus).',
        tags: ['Private']
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des leçons accessibles',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                required: ['lessonId', 'lessonTitle', 'cursusId', 'cursusTitle', 'lessonPath'],
                properties: [
                    new OA\Property(property: 'lessonId', type: 'integer', example: 12),
                    new OA\Property(property: 'lessonTitle', type: 'string', example: 'Leçon n°1 : Découverte'),
                    new OA\Property(property: 'cursusId', type: 'integer', example: 3),
                    new OA\Property(property: 'cursusTitle', type: 'string', example: 'Cursus guitare'),
                    new OA\Property(property: 'lessonPath', type: 'string', example: '/lessons/12'),
                ]
            )
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]

    /**
     * Controller to list lessons accessible to the authenticated user.
     */
    public function __invoke(LessonRepository $lessonRepository): JsonResponse
    {
        // Get the currently authenticated user
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }

        // Fetch lessons accessible to the user
        $rows = $lessonRepository->findAccessibleLessonsForUser($user);

        // Added a field for the front-end to link to the lesson
        $data = array_map(static function (array $row): array {
            return [
                'lessonId' => (int) $row['lessonId'],
                'lessonTitle' => (string) $row['lessonTitle'],
                'cursusId' => (int) $row['cursusId'],
                'cursusTitle' => (string) $row['cursusTitle'],
                'lessonPath' => '/lessons/' . (int) $row['lessonId'],
            ];
        }, $rows);

        return $this->json($data, 200);
    }
}
