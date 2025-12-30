<?php

namespace App\Controller\Api\Admin;

use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin user management API endpoints
 */
final class UserAdminController extends AbstractController
{
    #[Route('/api/admin/users', name: 'api_admin_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Lister les utilisateurs (admin)',
        tags: ['Admin - Users']
    )]

    // Query parameters (pagination, sorting, filters)
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20))]
    #[OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'createdAt'))]
    #[OA\Parameter(name: 'order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc'))]
    #[OA\Parameter(name: 'email', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'isVerified', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'))]

    // 200 OK response
    #[OA\Response(
        response: 200,
        description: 'Liste paginée des utilisateurs',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'email', type: 'string', example: 'admin@example.com'),
                            new OA\Property(property: 'isVerified', type: 'boolean', example: true),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ]
                    )
                ),
                new OA\Property(
                    property: 'meta',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'page', type: 'integer', example: 1),
                        new OA\Property(property: 'limit', type: 'integer', example: 20),
                        new OA\Property(property: 'totalItems', type: 'integer', example: 42),
                        new OA\Property(property: 'totalPages', type: 'integer', example: 3),
                        new OA\Property(property: 'sort', type: 'string', example: 'createdAt'),
                        new OA\Property(property: 'order', type: 'string', example: 'desc'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]

    //List users with pagination, sorting, and filtering
    public function listUsers(Request $request, UserRepository $users): JsonResponse
    {
        //Pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        // Prevent abusive queries
        $limit = min(100, max(1, $limit));

        // Filters
        $email = $request->query->get('email');
        $isVerified = $request->query->get('isVerified');

        // Sorting
        $sort = (string) $request->query->get('sort', 'createdAt');
        $order = strtolower((string) $request->query->get('order', 'desc'));
        $allowedSort = ['id', 'email', 'createdAt', 'isVerified'];

        if (!in_array($sort, $allowedSort, true)) {
            return $this->json(['message' => 'Invalid sort field.'], 400);
        }
        if (!in_array($order, ['asc', 'desc'], true)) {
            return $this->json(['message' => 'Invalid order value.'], 400);
        }

        // Normalized isVerified
        $isVerifiedBool = null;
        if ($isVerified !== null) {
            $val = strtolower((string) $isVerified);
            $isVerifiedBool = in_array($val, ['1', 'true', 'yes'], true) ? true : (in_array($val, ['0', 'false', 'no'], true) ? false : null);
        }

        // Fetch paginated users with filters
        $paginator = $users->searchPaginated(
            page: $page,
            limit: $limit,
            email: is_string($email) ? $email : null,
            isVerified: $isVerifiedBool,
            sort: $sort,
            order: $order
        );

        // Total items depends on repository implementation
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        // Map entities to a simple array
        $items = [];
        foreach ($paginator as $user) {
            $items[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
                'createdAt' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        // Final JSON response
        return $this->json([
            'items' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'sort' => $sort,
                'order' => $order,
            ]
        ]);
    }
}
