<?php

// src/Controller/User/Reset/ResetPasswordController.php

namespace App\Controller\User\Reset;

use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\User\User;
use App\Form\User\Reset\ChangePasswordForm;
use App\Form\User\Reset\ResetPasswordRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait as ResetPassword;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPassword;
    use Cart;

    public function __construct(private ResetPasswordHelperInterface $resetPasswordHelper, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Display & process form to request a password reset.
     */
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        $form = $this->createForm(ResetPasswordRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->resetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('profile/index.html.twig', [
            'cart' => $this->cart,
            'requestForm' => $form,
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function checkEmail(): Response
    {
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('profile/index.html.twig', [
            'cart' => $this->cart,
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    public function reset(Request $request, UserPasswordHasherInterface $userPasswordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('reset_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode(hash) the plain password, and set it.
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('profile/index.html.twig', [
            'cart' => $this->cart,
            'resetForm' => $form,
        ]);
    }

    private function resetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('reset_password_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return $this->redirectToRoute('reset_password_request');
        }
        //
        // $email = (new TemplatedEmail())
        //     ->from(new Address('crosswire.app@gmail.com', 'Please'))
        //     ->to($user->getEmail())
        //     ->subject('Your password reset request')
        //     ->htmlTemplate('reset_password/email.html.twig')
        //     ->context([
        //         'resetToken' => $resetToken,
        //     ])
        // ;
        //
        // $mailer->send($email);

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
        $mailer->send($email);

        // $emailer->sendEmail($user, $resetToken);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('reset_password_check_email');
    }
}
