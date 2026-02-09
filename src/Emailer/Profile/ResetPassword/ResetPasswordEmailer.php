<?php

// src/Emailer/Profile/ResetPassword/ResetPasswordEmailer.php

namespace App\Emailer\Profile\ResetPassword;

use App\Entity\User\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as Route;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class ResetPasswordEmailer
{
    public function __construct(private readonly MailerInterface $mailer, protected RequestStack $requestStack, private Route $route)
    {
    }

    /**
     * Send a templated email to confirm password reset.
     */
    public function sendEmail(User $user, ResetPasswordToken $resetToken)
    {
        $request = $this->requestStack->getCurrentRequest();

        $email = (new TemplatedEmail())
            // ->from(new Address('crosswire.app@gmail.com', 'Please Confirm Your Password Reset'))
            // ->to($user->getEmail())
            ->from(Address::create('Please Confirm Your Password Reset <crosswire.app@gmail.com>'))
            // ->to(new Address($user->getEmail()))
            ->to(new Address('vipulranganathan@outlook.com'))

            ->subject('Reset your password')
            ->htmlTemplate('emailer/profile/reset_password/index.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // Display flash error message or try to resend the message
            $request
                 ->getSession()
                 ->getFlashBag()
                 ->add(
                     'email-failure',
                     'Your password reset email was not sent successfully! We shall attempt to resend it.'
                 );

            throw new RedirectResponse($this->route->generate('reset_password_request'));
        }
    }
}
