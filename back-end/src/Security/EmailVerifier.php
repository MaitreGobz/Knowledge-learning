<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Centralizes email verification logic (signed URL generation + validation).
 */
final class EmailVerifier
{
    /**
     * EmailVerifier service's constructor
     */
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Send an email containing the registration confirmation link.
     */
    public function sendEmailConfirmation(string $verifyRouteName, User $user, TemplatedEmail $email): void
    {
        // Generate a signed URL bound to the user id and email.
        $signature = $this->verifyEmailHelper->generateSignature(
            $verifyRouteName,
            (string) $user->getId(),
            (string) $user->getEmail(),
            // Required route parameter for URL generation
            ['id' => $user->getId()]
        );

        // Retrieve existing Twig context
        $context = $email->getContext();
        // Inject verification data into the email template
        $context['signedUrl'] = $signature->getSignedUrl();
        $context['expiresAtMessageKey'] = $signature->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signature->getExpirationMessageData();

        // Update the email context with verification information
        $email->context($context);

        // Send the email via Symfony Mailer
        $this->mailer->send($email);
    }

    /**
     * Validates the email confirmation request and activates the user account.
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        // Validate the signed URL contained in the request.
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        // Mark the user account as verified
        $user->setIsVerified(true);

        // Persist the verification state in database
        $this->em->persist($user);
        $this->em->flush();
    }
}
