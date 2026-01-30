<?php

namespace App\Controller\Api\Private;

use OpenApi\Attributes as OA;
use App\Entity\Certification;
use App\Entity\User;
use App\Repository\CertificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to handle certification-related API endpoints.
 */
final class CertificationController extends AbstractController
{
    #[Route('/api/certifications', name: 'api_certifications_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/certifications',
        summary: 'Lister les certifications de l\'utilisateur connecté',
        description: 'Récupère toutes les certifications obtenues par l\'utilisateur actuellement authentifié.',
        tags: ['Private']
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des certifications récupérées avec succès',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                required: ['themeId', 'themeTitle', 'validatedAt'],
                properties: [
                    new OA\Property(property: 'themeId', type: 'integer', example: 5),
                    new OA\Property(property: 'themeTitle', type: 'string', example: 'Thème A'),
                    new OA\Property(
                        property: 'validatedAt',
                        type: 'string',
                        format: 'date-time',
                        example: '2024-01-15T10:00:00+01:00'
                    ),
                ]
            )
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]

    /**
     * List certifications for the currently authenticated user.
     */
    public function listCertifications(CertificationRepository $certificationRepository): JsonResponse
    {
        // Get the currently authenticated user
        $user = $this->getUser();

        // Ensure the user is authenticated
        if (!$user instanceof User) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        // Fetch certifications for the user
        $certifications = $certificationRepository->findByUserId($user->getId());

        // Format the data for the response
        $data = array_map(static function (Certification $cert) {
            return [
                'themeId' => $cert->getTheme()->getId(),
                'themeTitle' => $cert->getTheme()->getTitle(),
                'validatedAt' => $cert->getValidatedAt()->format(DATE_ATOM),
            ];
        }, $certifications);

        return $this->json($data, 200);
    }
}
