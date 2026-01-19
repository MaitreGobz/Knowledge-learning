<?php

namespace App\Controller\Api\Public;

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
        summary: 'Afficher la liste des thèmes avec un aperçu des cursus',
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
                            new OA\Property(
                                property: 'cursus',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 10),
                                        new OA\Property(property: 'title', type: 'string', example: "Cursus d'initiation à la guitare"),
                                        new OA\Property(property: 'description', type: 'string', nullable: true, example: "Lorem ipsum..."),
                                        new OA\Property(property: 'price', type: 'integer', example: 50),
                                    ]
                                )
                            )
                        ]
                    )
                )
            )
        ]
    )]
    /**
     * Lists all themes ordered by their ID.
     */
    public function list(ThemeRepository $themeRepository): JsonResponse
    {
        // Fetch all themes with their cursus preview.
        $themes = $themeRepository->findAllWithCursusPreview();

        // Prepare the response data
        $data = array_map(static function ($theme): array {
            $cursusPreview = [];

            // Build cursus preview data
            foreach ($theme->getCursus() as $cursus) {
                if ($cursus->isActive() !== true) {
                    continue;
                }

                $cursusPreview[] = [
                    'id' => $cursus->getId(),
                    'title' => $cursus->getTitle(),
                    'description' => $cursus->getDescription(),
                    'price' => $cursus->getPrice(),
                ];
            }

            // Return theme data with cursus preview
            return [
                'id' => $theme->getId(),
                'title' => $theme->getTitle(),
                'slug' => $theme->getSlug(),
                'description' => $theme->getDescription(),
                'cursus' => $cursusPreview,
            ];
        }, $themes);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
