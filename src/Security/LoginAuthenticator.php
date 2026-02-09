<?php

// src/Security/LoginAuthenticator.php

namespace App\Security;

use App\Repository\User\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

// use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserRepository $userRepo, private Security $security)
    {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    // public function supports(Request $request): ?bool
    // {
    //     // "auth-token" is an example of a custom, non-standard HTTP header used in this application
    //     return $request->headers->has('auth-token');
    // }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getRequestUri();
    }

    public function authenticate(Request $request): Passport
    {
        // Retrieve 'email' value from Request parameter.
        $email = $request->request->get('email', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /* Verify if user is deactivated. If so, logout instantly
          and redirect to User Deactivated page */
        if (!empty($request->request->get('email'))) {
            $email = $request->request->get('email');
            $user = $this->userRepo->findOneBy(['email' => $email]);
            if (!empty($user->getUserDeactivate())) {
                if ($user->getUserDeactivate()->isDeactivate()) {
                    $this->security->logout();

                    return new RedirectResponse($this->urlGenerator->generate('user_deactivated'));
                }
            }
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);

        /* Redirect to previous browsing page after being logged in.
         If no previous browsing page is found, redirect to  the 'store' page. */
        if ($request->request->has('_target_path')
            || $request->query->has('_target_path')) {
            $targetPath = $request->request->get('_target_path') ?: $request->query->get('_target_path');

            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('store'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
