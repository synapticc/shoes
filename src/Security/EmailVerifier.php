<?php

// src/Security/EmailVerifier.php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(private readonly VerifyEmailHelperInterface $verifyEmailHelper, private readonly MailerInterface $mailer, private readonly EntityManagerInterface $entityManager, private readonly Encryptor $encryptor)
    {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        /*
          - Generate email link
          - Encrypt the user ID before the adding to the link.
         */
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $this->encryptor->encrypt($user->getId())]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        /*
          // link generated:
          http://www.shoe.store/verify/email?
          expires = 1646912486&
          id = WOEDZ0Ljm71YGtCtMJOL1t4&
          signature = IAt09Pn1SRkP4u%2FqJ2EjaMnTKVod1%2BFygOhlBpUkjss%3D&
          token = YnZIDILMJl4fCgj%2BRzRGnb4mozKIE%2BGhWdGXem31494%3D
        */
        // Generate email
        $email->context($context);

        // Send email
        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            $user->getId(),
            $user->getEmail()
        );

        // On receiving the confirmation email, set the is_verified status
        // to true and update.
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
