<?php

namespace App\Controller\Api\Admin;

use OpenApi\Attributes as OA;
use App\Repository\CursusRepository;
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
        summary: 'Lister les cursus (admin)',
        tags: ['Admin - Cursus']
    )]

    #[OA\Response(
        response: 200,
        description: 'Liste des cursus actifs',
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
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]

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
