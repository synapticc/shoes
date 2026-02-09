<?php

// src/Emailer/Security/Register/RegisterUserConfirmEmailer.php

namespace App\Emailer\Security\Register;

use App\Entity\User\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class RegisterUserConfirmEmailer
{
    /**
     * Compose a templated email upon registering.
     */
    public function composeEmail(User $user): TemplatedEmail
    {
        $templatedEmail =
            (new TemplatedEmail())
                ->from(new Address('crosswire.app@gmail.com', 'Please Confirm your Email'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('emailer/security/register/confirmation_email.html.twig')
                ->context([
                    'firstName' => $user->getFirstName(),
                ])
        ;

        return $templatedEmail;
    }
}
