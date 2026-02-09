<?php

// src/Emailer/Profile/DeleteAccount/DeleteAccountInfoEmailer.php

namespace App\Emailer\Profile\DeleteAccount;

use App\Entity\User\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Prepare and send a templated email upon deactivating an account.
 */
class DeleteAccountInfoEmailer
{
    public function __construct(private readonly MailerInterface $mailer, protected RequestStack $requestStack)
    {
    }

    /**
     * Send a templated email.
     */
    public function sendEmail(User $user)
    {
        $request = $this->requestStack->getCurrentRequest();
        // Retrieve email address
        $emailAddress = $user->getEmail();

        $email = (new TemplatedEmail())
          ->from(Address::create('Belladonna Shoes <crosswire.app@gmail.com>'))
          ->to(new Address($emailAddress))
          ->subject('Account Deactivated!')
          // ->htmlTemplate('emailer/delete_account/index.html.twig')
          ->textTemplate('emailer/profile/delete_account/index.html.twig')
          ->context([
              'deletedEmail' => $emailAddress,
              'firstName' => $user->getFirstName(),
              'lastName' => $user->getLastName(),
          ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // Flash error message
            $request
                 ->getSession()
                 ->getFlashBag()
                 ->add(
                     'email-failure',
                     'Your account deactivation email was not sent successfully! We shall attempt to resend it.'
                 );

            return $this->redirectToRoute('user_profile');
        }
    }
}
