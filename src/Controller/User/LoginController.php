<?php

// src/Controller/User/LoginController.php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * Login portal for existing (verified & non-verified) user.
     * Retrieve the last remembered name.
     * Display authentication error.
     * Retrieve exact URL of previous page and pass it to twig as hidden field.
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('store', [], Response::HTTP_SEE_OTHER);
        }
        /* Retrieve previous URL
        $request->headers->get('referer') =>
        // Ex. "http://shoe.shop/Clark-s/Fallhill-Mid-Chukka/8ZAZJ13do4D0zP_Lq1pJfU_o"
        */

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'back_to_previous_page' => $request->headers->get('referer'),
        ]);
    }

    /**
     * Logout function
     * Redirect to Store Homepage after logout
     * To be configured in config/packages/security.yaml.
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
