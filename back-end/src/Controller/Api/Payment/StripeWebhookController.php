<?php

namespace App\Controller\Api\Payment;

use OpenApi\Attributes as OA;
use App\Service\Payment\StripeWebhookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 *  Handle endpoint for Stripe webhooks.
 */
final class StripeWebhookController extends AbstractController
{
    #[Route('/api/stripe/webhook', name: 'api_stripe_webhook', methods: ['POST'])]
    #[OA\Post(
        path: '/api/stripe/webhook',
        summary: 'Endpoint pour les webhooks Stripe',
        description: 'Reçoit et traite les événements webhook envoyés par Stripe.',
        tags: ['Payment'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Webhook reçu et traité avec succès.'
    )]
    #[OA\Response(
        response: 400,
        description: 'Signature manquante ou invalide.'
    )]

    /**
     *  Handle Stripe webhook requests.
     */
    public function __invoke(
        Request $request,
        StripeWebhookService $stripeWebhookService,
        string $stripeWebhookSecret,
    ): Response {
        // Retrieve the request's body and parse it as JSON
        $payload = $request->getContent();
        $signedHeader = $request->headers->get('stripe-signature');

        //
        if (!$signedHeader) {
            return new Response('Signature manquante', 400);
        }

        // Verify the webhook signature
        try {
            $event = Webhook::constructEvent($payload, $signedHeader, $stripeWebhookSecret);
        } catch (\Throwable) {
            return new Response('Signature invalide', 400);
        }

        // Handle the event
        $stripeWebhookService->handle($event);

        return new Response('Webhook reçu', 200);
    }
}
