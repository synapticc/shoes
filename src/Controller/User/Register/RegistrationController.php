<?php

// src/Controller/User/Register/RegistrationController.php

namespace App\Controller\User\Register;

use App\Emailer\Security\Register\RegisterUserConfirmEmailer;
use App\Entity\User\User;
use App\Form\User\Register\RegistrationForm;
use App\Repository\User\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly EmailVerifier $emailVerifier)
    {
    }

    /**
     * Register new user.
     * Handle registration form.
     * Hash the new password.
     * Create UUID for each user.
     * Verify the email of newly registered user by sending a link to that email.
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginAuthenticator $authenticator,
        EntityManagerInterface $em,
        RegisterUserConfirmEmailer $emailer,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plaintextPassword = $form->get('plainPassword')->getData();

            // ---- Hash permanently user password -----
            $hashedPassword =
                $passwordHasher->hashPassword($user, $plaintextPassword);

            // ---- Generate unique ID -----
            $uniqueId = bin2hex(random_bytes(16));
            $user->setPassword($hashedPassword)
                 ->setUuid($uniqueId);

            // encode the plain password
            $user->setPassword(
                $passwordHasher
                    ->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
            );

            $em->persist($user);
            $em->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier
                    ->sendEmailConfirmation(
                        'app_verify_email',
                        $user,
                        $emailer->composeEmail($user)
                    );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render(
            'security/sign-up/register.html.twig',
            ['signUpForm' => $form]
        );
    }

    /**
     * Confirm if the email confirmation link is valid.
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        /*
          // Retrieve id from email link:
          http://www.shoe.store/verify/email?
          expires = 1646912486&
  ==>   id = WOEDZ0Ljm71YGtCtMJOL1t4&
          signature = IAt09Pn1SRkP4u%2FqJ2EjaMnTKVod1%2BFygOhlBpUkjss%3D&
          token = YnZIDILMJl4fCgj%2BRzRGnb4mozKIE%2BGhWdGXem31494%3D
        */

        $id = $request->get('id');
        // $id = WOEDZ0Ljm71YGtCtMJOL1t4

        // Decrypt the user ID from the link
        $id = $this->encryptor->decrypt($id);
        // $id = 2

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }
        $user = $userRepository->find($id);
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // Validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('store');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('store');
    }
}
