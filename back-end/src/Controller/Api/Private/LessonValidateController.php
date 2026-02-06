<?php

namespace App\Controller\Api\Private;

use App\Entity\Lesson;
use App\Repository\LessonRepository;
use App\Repository\LessonValidationRepository;
use App\Service\Private\LessonProgressService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to handle lesson validation endpoints.
 */
final class LessonValidateController extends AbstractController
{
    #[Route('/api/private/lessons/{id}/validate', name: 'api_private_lesson_validate', methods: ['POST'])]
    #[OA\Post(
        path: '/api/private/lessons/{id}/validate',
        summary: 'Valider une leçon (accès soumis à achat)',
        description: 'Valide la leçon pour l\'utilisateur connecté. Si toutes les leçons du cursus sont validées, le cursus est validé automatiquement.',
        tags: ['Private']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de la leçon à valider',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Leçon validée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'lessonId', type: 'integer', example: 100),
                new OA\Property(property: 'lessonValidated', type: 'boolean', example: true),
                new OA\Property(property: 'cursusId', type: 'integer', example: 10),
                new OA\Property(property: 'cursusValidated', type: 'boolean', example: false),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (achat requis)')]
    #[OA\Response(response: 404, description: 'Leçon introuvable')]
    #[OA\Response(response: 409, description: 'Leçon déjà validée')]

    /**
     * Validate a lesson for the authenticated user.
     */
    public function validateLesson(int $id, LessonRepository $lessonRepository, LessonProgressService $service): JsonResponse
    {
        // Fetch lesson
        $lesson = $lessonRepository->find($id);
        if (!$lesson || !$lesson->isActive()) {
            return $this->json(['message' => 'Leçon introuvable.'], 404);
        }

        // Get authenticated user
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }

        // Access rule check
        $this->denyAccessUnlessGranted('LESSON_VIEW', $lesson);

        // Validate lesson
        try {
            $result = $service->validateLesson($user, $lesson);

            return $this->json([
                'lessonId' => $lesson->getId(),
                'lessonValidated' => true,
                'cursusId' => $result['cursusId'],
                'cursusValidated' => $result['cursusValidated'],
            ], 200);
        } catch (\LogicException) {
            return $this->json(['message' => 'Leçon déjà validée.'], 409);
        }
    }

    #[Route('/api/private/lessons/{id}/validated', name: 'api_private_lesson_is_validated', methods: ['GET'])]
    #[OA\Get(
        path: '/api/private/lessons/{id}/validated',
        summary: 'Vérifier si une leçon est validée',
        description: 'Vérifie si la leçon est validée pour l\'utilisateur connecté.',
        tags: ['Private']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de la leçon à vérifier',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Statut de validation de la leçon',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'lessonId', type: 'integer', example: 100),
                new OA\Property(property: 'validated', type: 'boolean', example: true),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 404, description: 'Leçon introuvable')]
    #[OA\Response(response: 409, description: 'Leçon déjà validée')]

    /**
     * Check if a lesson is validated for the authenticated user.
     */
    public function validationState(int $id, LessonRepository $lessonRepository, LessonValidationRepository $lessonValidationRepository): JsonResponse
    {
        // fetch lesson and check if it exists and is active
        $lesson = $lessonRepository->find($id);
        if (!$lesson || !$lesson->isActive()) {
            return $this->json(['message' => 'Leçon introuvable.'], 404);
        }

        // Get authenticated user
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }

        // Access rule check
        $this->denyAccessUnlessGranted('LESSON_VIEW', $lesson);

        // Check if validation exists
        $existingValidation = $lessonValidationRepository->findOneBy(['user' => $user, 'lesson' => $lesson]);

        // Return validation status
        return $this->json([
            'lessonId' => $lesson->getId(),
            'validated' => (bool)$existingValidation,
        ], 200);
    }
}
