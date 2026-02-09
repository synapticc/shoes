<?php

// src/Controller/User/Reset/ResetEmailController.php

namespace App\Controller\User\Reset;

use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Emailer\Security\Register\RegisterUserConfirmEmailer as RegisterEmailer;
use App\Form\User\Reset\ChangeEmailForm;
use App\Form\User\Reset\ResetEmailRequestForm;
use App\Repository\User\UserRepository as Repo;
use App\Security\EmailVerifier;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class ResetEmailController extends AbstractController
{
    use Cart;

    public function __construct(
        private Encryptor $encryptor,
        private EmailVerifier $emailVerifier,
    ) {
    }

    public function request(Request $r)
    {
        if ($this->getUser()) {
            $user = $this->getUser();
            $email = $this->getUser()->getUserIdentifier();
            $hashedPassword = $this->getUser()->getPassword();

            // Retrieve cart items and cart product images
            $this->cart($this->getUser(), null);

            $form = $this->createForm(ResetEmailRequestForm::class);
            $form->handleRequest($r);

            if ($form->isSubmitted() && $form->isValid()) {
                $plaintextPassword = $form->get('plainPassword')->getData();
                $submittedToken = $r->request->get('token');

                // Configure different password hashers via the factory
                $factory = new PasswordHasherFactory([
                    'common' => ['algorithm' => 'bcrypt'],
                    'memory-hard' => ['algorithm' => 'sodium'],
                ]);

                // Retrieve the right password hasher by its name
                $passwordHasher = $factory->getPasswordHasher('common');

                // Verify that a given plain password matches the hash
                $isSamePassword = $passwordHasher->verify($hashedPassword, $plaintextPassword);

                if (true == $isSamePassword) {
                    return $this->redirectToRoute('app_change_email', [
                        'token' => $submittedToken,
                    ], Response::HTTP_SEE_OTHER);
                } elseif (false == $isSamePassword) {
                    return $this->redirectToRoute('app_change_email_request', [], Response::HTTP_SEE_OTHER);
                }
            }

            return $this->render('profile/index.html.twig', [
                'cart' => $this->cart,
                'requestForm' => $form,
            ]);
        }
    }

    /**
     * Display & process form to request a email reset.
     */
    public function reset(Request $r, RegisterEmailer $emailer, string $token): Response
    {
        if ($this->getUser()) {
            // Retrieve cart items and cart product images
            $this->cart($this->getUser(), null);

            if (false == $this->isCsrfTokenValid('reset-email', $token)) {
                return $this->redirectToRoute('app_change_email_request', [], Response::HTTP_SEE_OTHER);
            }

            $user = $this->getUser();
            $currentEmail = $this->getUser()->getUserIdentifier();
            $hashedPassword = $this->getUser()->getPassword();

            $form = $this->createForm(ChangeEmailForm::class);
            $form->handleRequest($r);

            if ($form->isSubmitted() && $form->isValid()) {
                $newEmail = $form->get('email')->getData();

                if ($currentEmail === $newEmail) {
                    $msg = 'Enter a new email. The email entered is the same as the current email.';
                    $this->addFlash('same_email_error', $msg);

                    return $this->redirectToRoute('app_change_email', [
                        'token' => $token,
                    ], Response::HTTP_SEE_OTHER);
                }

                // Update the new email and set isVerified to false
                // since we now have a new email address.
                $user->setEmail($newEmail)
                     ->setIsVerified(false);

                // generate a signed url and email it to the user
                $this->emailVerifier
                     ->sendEmailConfirmation(
                         'app_verify_new_changed_email',
                         $user,
                         $emailer->composeEmail($user)
                     );

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('user_profile', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('profile/index.html.twig', [
                'cart' => $this->cart,
                'requestForm' => $form,
            ]);
        }
    }

    public function verifyNewEmail(Request $r, Repo $repo): Response
    {
        $hashedId = $r->get('id');
        $id = $this->encryptor->decrypt($hashedId);

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $repo->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($r, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('email_verified', 'Your email address has been verified.');

        return $this->redirectToRoute('store');
    }
}
