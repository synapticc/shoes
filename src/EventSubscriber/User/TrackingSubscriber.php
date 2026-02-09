<?php

// src/EventSubscriber/User/TrackingSubscriber.php

namespace App\EventSubscriber\User;

use App\Entity\Billing\Order;
use App\Entity\User\Log\LoginReport;
use App\Entity\User\Log\LogoutReport;
use App\Entity\User\Session\PageView;
use App\Entity\User\Session\Session;
use App\Entity\User\User;
use App\Repository\Product\ProductData\ProductDataRepository;
use App\Repository\User\Log\LogoutReportRepository;
use App\Repository\User\Session\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Track all user visits.
 * Each time a page is visited, a log of the page route, page URL, date of
 * visit, referer URL and user (if logged) are stored automatically.
 */
class TrackingSubscriber implements EventSubscriberInterface
{
    public const COOKIE_TRACKER = 'tracker';

    /**
     * Routes that should not be tracked.
     */
    private const BLOCKED_PATHS = [
        '' => true,
        '_wdt' => true,
        '_wdt_stylesheet' => true,
        '_profiler' => true,
        'add_to_cart' => true,
        'deactivate_cart' => true,
        'new_cart' => true,
        'update_cart_item' => true,
        'remove_cart_item' => true,
        'retrieve_cart_item' => true,
        'update_cart' => true,
        'clear_cart' => true,
        'delete_cart' => true,
        'latest_cart' => true,
        'update_list' => true,
        'update_total' => true,
        'admin_users_edit' => true,
        'review_helpful' => true,
        'review_unhelpful' => true,
        'search' => true];

    private ?Session $trackingSession = null;

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly Encryptor $encryptor, private readonly ProductDataRepository $productDataRepo, private readonly LogoutReportRepository $logoutReportRepo, private readonly SessionRepository $sessionRepo, private readonly RequestStack $requestStack, private readonly RouterInterface $router)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
            KernelEvents::RESPONSE => ['onKernelResponse', 100],
            LoginSuccessEvent::class => ['onLoginSuccess', 200],
            LogoutEvent::class => ['onLogout', 10],
        ];
    }

    /**
     * Track page visits at the beginning of the request.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($this->isBlockedPath($route)) {
            return;
        }

        $this->trackingSession = $this->getOrCreateSession($request);
    }

    /**
     * Save page view and flush changes at the end of the request.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || null === $this->trackingSession) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($this->isBlockedPath($route)) {
            return;
        }

        $response = $event->getResponse();
        $this->addPage($request, $response);

        // Ensure changes are saved at end of request
        $this->entityManager->flush();
    }

    /**
     * Each successful login is stored alongside the user,
     * the IP address, the user agent and the login datetime.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($this->trackingSession && $this->trackingSession->getLoginReport()) {
            $response->headers->clearCookie(self::COOKIE_TRACKER);
            $this->trackingSession = $this->createSession($request);
        }

        /* If the current session already has its own Login,
           create a new one, since each session should have
           at most one Login and one Logout.
        */
        $this->addLogin($event->getUser());
        $this->entityManager->flush();
    }

    /**
     * Each logout is stored alongside the user and
     * the logout datetime.
     */
    public function onLogout(LogoutEvent $event): void
    {
        if (!$this->trackingSession || !$this->trackingSession->getLoginReport()) {
            return;
        }
        $logoutReport = (new LogoutReport())
            ->setLoginReport($this->trackingSession->getLoginReport())
            ->setCreated(new \DateTime());

        if ($response = $event->getResponse()) {
            $response->headers->clearCookie(self::COOKIE_TRACKER);
            $response->headers->clearCookie(Order::COOKIE_CART);
        }
        $this->entityManager->persist($logoutReport);
        $this->entityManager->flush();
    }

    /**
     * Get existing session or create a new one.
     */
    private function getOrCreateSession(Request $request): Session
    {
        $cookieValue = $request->cookies->get(self::COOKIE_TRACKER);
        if ($cookieValue) {
            $session = $this->sessionRepo->find($cookieValue);
            if ($session) {
                return $session;
            }
        }

        return $this->createSession($request);
    }

    /**
     * Check if the current route should be tracked.
     */
    private function isBlockedPath(?string $route): bool
    {
        return null !== $route && isset(self::BLOCKED_PATHS[$route]);
    }

    /**
     * A session is an object which binds the user, the device info,
     * the IP address, the user agent and the logout/login datetime
     * together.
     */
    public function createSession(Request $request): Session
    {
        $session = (new Session())
            ->setIpAddress($request->getClientIp() ?? '')
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setCreated(new \DateTime());

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    /**
     * Record a page view for the current session.
     */
    private function addPage(Request $request, Response $response): void
    {
        $this->ensureCookieIsSet($request, $response);
        $route = $request->attributes->get('_route');
        $pageView = $this->createPageView($request, $route);
        $this->entityManager->persist($pageView);
        $this->trackingSession->addPageView($pageView);
    }

    /**
     * Ensure the tracking cookie is set on the response.
     */
    private function ensureCookieIsSet(Request $request, Response $response): void
    {
        if ($request->cookies->has(self::COOKIE_TRACKER)) {
            return;
        }

        $cookie = Cookie::create(self::COOKIE_TRACKER)
          ->withValue((string) $this->trackingSession->getId())
          ->withHttpOnly(true)
          ->withSecure($request->isSecure())
          ->withSameSite(Cookie::SAMESITE_LAX);
        $response->headers->setCookie($cookie);
    }

    /**
     * Create a PageView entity.
     */
    private function createPageView(Request $request, string $route): PageView
    {
        $pageView = (new PageView())
          ->setUrl($request->getUri())
          ->setRoute($route)
          ->setReferer((string) $request->headers->get('referer', ''))
          ->setSession($this->trackingSession)
          ->setCreated(new \DateTime());

        /* Save product visited to each session */
        if ('store_details' === $route) {
            if ($productId = $request->attributes->get('id')) {
                $pageView->setProduct($this->productDataRepo->find($productId));
            }
        }

        /* Save all queries passed in the main store */
        if ('store' === $route) {
            $pageView->setQueryParameters($request->query->all());
        }

        return $pageView;
    }

    public function addLogin(User $user): Session
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return $this->trackingSession ?? $this->createSession();
        }
        $loginReport = new LoginReport();
        $loginReport->setSession($this->trackingSession)
                    ->setCreated(new \DateTime());

        /* Update cookie session. */
        $this->trackingSession
            ->setLoginReport($loginReport)
            ->setUsers($user);

        $this->entityManager->persist($this->trackingSession);
        $this->entityManager->flush();

        return $this->trackingSession;
    }
}
