<?php

namespace App\Controller\Api\Admin;

use App\Repository\CursusRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin cursus management API endpoints
 */
final class CursusAdminController extends AbstractController
{
    #[Route('/api/admin/cursus', name: 'api_admin_cursus_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/cursus',
        summary: 'Lister les cursus pour le back-office',
        description: 'Retourne la liste des cursus actifs (id + titre uniquement). Utilisé pour le rattachement lors de la création d’une leçon.',
        tags: ['Admin - Cursus'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des cursus',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 12),
                            new OA\Property(property: 'title', type: 'string', example: 'Cursus Symfony'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé'
            )
        ]
    )]

    /**
     * Lists active cursus for admin purposes.
     */
    public function listCursus(CursusRepository $cursusRepository): JsonResponse
    {
        // Fetch active cursus ordered by creation date descending
        $cursusList = $cursusRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC']
        );

        $data = [];

        // Transform cursus entities to simple arrays
        foreach ($cursusList as $cursus) {
            $data[] = [
                'id' => $cursus->getId(),
                'title' => $cursus->getTitle(),
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
