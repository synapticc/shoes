<?php

// src/Emailer/Security/ForgotPassword/ForgotPasswordEmailer.php

namespace App\Emailer\Security\ForgotPassword;

use App\Entity\User\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class ForgotPasswordEmailer
{
    public function __construct(private readonly MailerInterface $mailer, protected RequestStack $requestStack)
    {
    }

    /**
     * Send a templated email to confirm resetting one's password.
     */
    public function sendEmail(User $user, ResetPasswordToken $resetToken)
    {
        $request = $this->requestStack->getCurrentRequest();

        $email = (new TemplatedEmail())
            // ->from(new Address('crosswire.app@gmail.com', 'Please Confirm Your Password Reset'))
            // ->to($user->getEmail())
            ->from(Address::create('Please Confirm Your Password Reset <crosswire.app@gmail.com>'))
            ->to(new Address($user->getEmail()))
            ->subject('Reset your password')
            ->htmlTemplate('emailer/security/forgot_password/index.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // some error prevented the email sending; display an
            // error message or try to resend the message

            $request
                 ->getSession()
                 ->getFlashBag()
                 ->add(
                     'email-failure',
                     'Your password reset email was not sent successfully! We shall attempt to resend it.'
                 );

            // retrieve messages
            // foreach ($session->getFlashBag()->get('email-failure', []) as $message)
            // {
            //     echo '<div class="flash-notice">'.$message.'</div>';
            // }

            return $this->redirectToRoute('forgot_reset_password_request');
        }
    }
}
