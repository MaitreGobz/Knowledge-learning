<?php

namespace App\Controller\Api\Private;

use OpenApi\Attributes as OA;
use App\Entity\Lesson;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to handle lesson-related API endpoints.
 */
final class LessonController extends AbstractController
{
    #[Route('/api/lessons/{id}', name: 'api_lesson_details', methods: ['GET'])]
    #[OA\Get(
        path: '/api/lessons/{id}',
        summary: 'Afficher le détail d\'une leçon (accès soumis à achat)',
        tags: ['Private']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
        example: 100
    )]
    #[OA\Response(
        response: 200,
        description: 'Leçon récupérée avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 100),
                new OA\Property(property: 'title', type: 'string', example: 'Leçon 1'),
                new OA\Property(property: 'content', type: 'string', example: 'Lorem ipsum...'),
                new OA\Property(property: 'videoUrl', type: 'string', nullable: true, example: 'https://...'),
                new OA\Property(property: 'position', type: 'integer', example: 1),
                new OA\Property(property: 'price', type: 'integer', example: 26),
                new OA\Property(property: 'cursusId', type: 'integer', example: 10),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (achat requis)')]
    #[OA\Response(response: 404, description: 'Leçon introuvable')]

    /**
     *  Show details of a specific lesson after access rights verification.
     */
    public function detailLesson(Lesson $lesson): JsonResponse
    {
        // Access rule check
        $this->denyAccessUnlessGranted('LESSON_VIEW', $lesson);

        // Prevent access to disabled lessons
        if (!$lesson->isActive()) {
            return new JsonResponse(['message' => 'Leçon introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Return lesson details
        return new JsonResponse([
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'content' => $lesson->getContent(),
            'videoUrl' => $lesson->getVideoUrl(),
            'position' => $lesson->getPosition(),
            'price' => $lesson->getPrice(),
            'cursusId' => $lesson->getCursus()?->getId(),
        ]);
    }
}
