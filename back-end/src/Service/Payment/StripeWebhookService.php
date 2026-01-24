<?php

namespace App\Service\Payment;

use App\Entity\AccessRight;
use App\Entity\Purchase;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Event;

final class StripeWebhookService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CursusRepository $cursusRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly PurchaseRepository $purchaseRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(Event $event): void
    {
        // We only care about completed checkout sessions
        if ($event->type !== 'checkout.session.completed') {
            return;
        }

        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        // Retrieve metadata
        $userId = $session->metadata['user_id'] ?? null;
        $itemType = $session->metadata['item_type'] ?? null;
        $itemId = $session->metadata['item_id'] ?? null;

        // Validate metadata
        if (!$userId || !$itemType || !$itemId) {
            $this->logger->warning('Stripe webhook missing metadata', ['eventId' => $event->id]);
            return;
        }

        // Idempotence via stripe session id (unique)
        $already = $this->purchaseRepository->findOneBy(['stripeSessionId' => $session->id]);
        if ($already) {
            return;
        }

        // Retrieve user
        $user = $this->userRepository->find((int) $userId);
        if (!$user) {
            $this->logger->warning('Stripe webhook user not found', ['userId' => $userId]);
            return;
        }

        // Create purchase record
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setStripeSessionId($session->id);
        $purchase->setAmount((int) ($session->amount_total ?? 0));
        $purchase->setCurrency((string) ($session->currency ?? 'eur'));
        $purchase->setStatus((string) ($session->payment_status ?? 'paid'));

        // Handle item type
        if ($itemType === 'cursus') {
            $cursus = $this->cursusRepository->find((int) $itemId);
            if (!$cursus) {
                $this->logger->warning('Stripe webhook cursus not found', ['itemId' => $itemId]);
                return;
            }

            // Set cursus on purchase
            $purchase->setCursus($cursus);
            $purchase->setLesson(null);

            $this->em->persist($purchase);
            $this->em->flush();

            // Access to cursus
            $this->persistAccessRight($user, $purchase, $cursus, null);

            // Access to all lessons of cursus
            foreach ($cursus->getLessons() as $lesson) {
                $this->persistAccessRight($user, $purchase, null, $lesson);
            }

            $this->em->flush();
            return;
        }

        // Handle lesson purchase
        if ($itemType === 'lesson') {
            $lesson = $this->lessonRepository->find((int) $itemId);
            if (!$lesson) {
                $this->logger->warning('Stripe webhook lesson not found', ['itemId' => $itemId]);
                return;
            }

            // Set lesson on purchase
            $purchase->setLesson($lesson);
            $purchase->setCursus(null);

            $this->em->persist($purchase);
            $this->em->flush();

            $this->persistAccessRight($user, $purchase, null, $lesson);
            $this->em->flush();
        }
    }

    /**
     *  Persist an access right for the user
     */
    private function persistAccessRight($user, Purchase $purchase, $cursus, $lesson): void
    {
        // Create access right
        $access = new AccessRight();
        $access->setUser($user);
        $access->setPurchase($purchase);
        $access->setGrantedAt(new \DateTime());

        // Set either cursus or lesson
        $access->setCursus($cursus);
        $access->setLesson($lesson);

        // Persist access right
        $this->em->persist($access);
    }
}
