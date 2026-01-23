<?php

namespace App\Controller\Api\Public;

use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to handle Cursus-related API endpoints.
 */
final class CursusController extends AbstractController
{
    #[Route('/api/cursus/{id}', name: 'api_cursus_details', methods: ['GET'])]
    #[OA\Get(
        path: '/api/cursus/{id}',
        summary: 'Afficher le détail d\’un cursus avec un aperçu des leçons',
        tags: ['Cursus']
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
        example: 10
    )]

    #[OA\Response(
        response: 200,
        description: 'Cursus récupéré avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 10),
                new OA\Property(property: 'title', type: 'string', example: "Cursus d'initiation à la guitare"),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Lorem ipsum...'),
                new OA\Property(property: 'price', type: 'integer', example: 50),
                new OA\Property(
                    property: 'lessons',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 100),
                            new OA\Property(property: 'title', type: 'string', example: 'Leçon 1'),
                            new OA\Property(property: 'price', type: 'integer', example: 26),
                            new OA\Property(property: 'position', type: 'integer', example: 1),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Cursus introuvable')]

    /**
     * Get details of a specific cursus by ID, including a preview of its lessons.
     */
    public function detailCursus(int $id, CursusRepository $cursusRepository, LessonRepository $lessonRepository): JsonResponse
    {
        // Fetch cursus (active only)
        $cursus = $cursusRepository->findActiveById($id);

        if ($cursus === null) {
            return new JsonResponse(['message' => 'Cursus introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Fetch lesson preview (active only, ordered)
        $lessons = $lessonRepository->findActiveByCursusIdOrdered($id);

        $lessonPreview = [];
        foreach ($lessons as $lesson) {
            $lessonPreview[] = [
                'id' => $lesson->getId(),
                'title' => $lesson->getTitle(),
                'price' => $lesson->getPrice(),
                'position' => $lesson->getPosition(),
            ];
        }

        // Return cursus data with lesson preview
        $data = [
            'id' => $cursus->getId(),
            'title' => $cursus->getTitle(),
            'description' => $cursus->getDescription(),
            'price' => $cursus->getPrice(),
            'lessons' => $lessonPreview,
        ];

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
