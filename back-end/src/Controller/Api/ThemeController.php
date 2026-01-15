<?php

namespace App\Controller\Api;

use App\Repository\ThemeRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to handle Theme-related API endpoints.
 */
final class ThemeController extends AbstractController
{
    #[Route('/api/themes', name: 'api_themes_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/themes',
        summary: 'List all themes',
        description: 'Retourne la liste des thèmes disponibles ordonnés par nom.',
        tags: ['Themes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Thèmes récupérés avec succès',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string', example: 'Fantasy'),
                            new OA\Property(property: 'slug', type: 'string', example: 'fantasy'),
                            new OA\Property(
                                property: 'description',
                                type: 'string',
                                nullable: true,
                                example: 'Contenu d\'apprentissage sur le thème de la fantasy'
                            ),
                        ]
                    )
                )
            )
        ]
    )]
    public function list(ThemeRepository $themeRepository): JsonResponse
    {
        // Fetch all themes ordered by their ID ascending
        $themes = $themeRepository->findBy([], ['id' => 'ASC']);

        /**
         * Transform the themes into an array suitable for JSON response.
         */
        $data = array_map(static function ($theme): array {
            return [
                'id' => $theme->getId(),
                'title' => $theme->getTitle(),
                'slug' => $theme->getSlug(),
                'description' => $theme->getDescription(),
            ];
        }, $themes);

        // Return a JSON response with HTTP 200 OK
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
