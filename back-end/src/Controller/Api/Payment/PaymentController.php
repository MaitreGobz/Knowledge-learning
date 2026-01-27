<?php

namespace App\Controller\Api\Payment;

use OpenApi\Attributes as OA;
use App\Dto\Payment\CheckoutRequest;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Service\Payment\CheckoutService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

final class PaymentController extends AbstractController
{
    #[Route('/api/payments/checkout', name: 'api_payments_checkout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/api/payments/checkout',
        summary: 'Créer une session Stripe Checkout (cursus/leçon)',
        tags: ['Payment']
    )]
    #[OA\Parameter(
        name: 'X-CSRF-TOKEN',
        in: 'header',
        required: true,
        description: 'Token CSRF requis (session cookie). CSRF id: auth',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['type', 'itemId'],
            properties: [
                new OA\Property(property: 'type', type: 'string', enum: ['cursus', 'lesson'], example: 'cursus'),
                new OA\Property(property: 'itemId', type: 'integer', example: 1),
            ]
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Session créée',
        content: new OA\JsonContent(
            type: 'object',
            required: ['sessionId', 'checkoutUrl'],
            properties: [
                new OA\Property(property: 'sessionId', type: 'string', example: 'cs_test_123'),
                new OA\Property(property: 'checkoutUrl', type: 'string', example: 'https://checkout.stripe.com/...'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Refusé (CSRF invalide ou item non achetable)')]
    #[OA\Response(response: 404, description: 'Item introuvable')]
    #[OA\Response(response: 409, description: 'Déjà acheté')]
    #[OA\Response(response: 422, description: 'Payload invalide')]
    #[OA\Response(response: 502, description: 'Erreur Stripe')]

    /**
     *  Handle checkout session creation for cursus or lesson purchase.
     */
    public function checkout(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CheckoutService $checkoutService,
        CursusRepository $cursusRepository,
        LessonRepository $lessonRepository,
        PurchaseRepository $purchaseRepository,
        LoggerInterface $logger
    ): JsonResponse {
        // Get the authenticated user
        $user = $this->getUser();

        // Ensure the user is authenticated
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // Deserialize and validate the request payload
        try {
            $dto = $serializer->deserialize(
                $request->getContent(),
                CheckoutRequest::class,
                'json'
            );
        } catch (\Throwable) {
            return $this->json(['message' => 'JSON invalide'], 422);
        }

        // Validate DTO
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        // Process checkout based on item type
        try {
            if ($dto->type === 'cursus') {
                $cursus = $cursusRepository->find($dto->itemId);
                if (!$cursus) {
                    return $this->json(['message' => 'Cursus introuvable'], 404);
                }

                if ($purchaseRepository->purchasedCursusByUser($user, $cursus)) {
                    return $this->json(['message' => 'Déjà acheté'], 409);
                }

                $result = $checkoutService->createCheckoutSessionForCursus($user, $cursus);
            } else {
                $lesson = $lessonRepository->find($dto->itemId);
                if (!$lesson) {
                    return $this->json(['message' => 'Leçon introuvable'], 404);
                }

                if ($purchaseRepository->purchasedLessonByUser($user, $lesson)) {
                    return $this->json(['message' => 'Déjà acheté'], 409);
                }

                $result = $checkoutService->createCheckoutSessionForLesson($user, $lesson);
            }
        } catch (ApiErrorException $e) {
            $logger->error('Stripe error', [
                'type' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'Erreur Stripe'], 502);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        } catch (\Throwable) {
            return $this->json(['message' => 'Erreur interne'], 500);
        }

        return $this->json($result, 200);
    }
}
