<?php

// src/Controller/Cart/CartTray/CartTrayTrait.php

namespace App\Controller\Cart\CartTray;

use App\Entity\Billing\Order;
use App\Entity\User\User;
use App\Repository\Billing\OrderRepository;
use App\Repository\Product\ProductData\ProductDataRepository;
use Doctrine\ORM\EntityManagerInterface as ORM;
use foroco\BrowserDetection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Inject array of cart items (to be used on the top right element)
 * Store pages.
 */
trait CartTrayTrait
{
    protected Order $order;
    protected ?array $cart = [];
    protected array $deviceInfo = [];
    protected RequestStack $requestStack;
    protected OrderRepository $orderRepo;
    protected ORM $em;
    protected ProductDataRepository $productDataRepo;

    #[Required]
    public function setRepository(
        RequestStack $requestStack,
        OrderRepository $orderRepo,
        ORM $em,
        ProductDataRepository $productDataRepo,
    ): void {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->orderRepo = $orderRepo;
        $this->productDataRepo = $productDataRepo;
    }

    /**
     * Retrieve the cart to be displayed on all Store pages.
     */
    protected function cart(?User $user)
    {
        $session = $this->requestStack->getSession();
        $request = $this->requestStack->getCurrentRequest();
        $userAgent = $request->headers->get('user-agent');
        $browser = new BrowserDetection();

        // Get cart ID from session
        $cartId = $session->get('cart_id');
        $activeCart = null;
        $activeCartId = null;

        if ($user) {
            // Authenticated user: ensure one active cart
            $userId = $user->getId();
            $activeCart = $this->orderRepo->activeCart($userId);
            if ($activeCart) {
                $activeCartId = $activeCart->getId();
            }

            if ($cartId && $activeCart) {
                if ($cartId !== $activeCartId) {
                    $currentCart = $this->orderRepo->find($cartId);
                    // $activeCart = $this->orderRepo->find($activeCartId);

                    if ($currentCart && $activeCart) {
                        foreach ($currentCart->getItems() as $item) {
                            $currentCart->removeItem($item);
                            $activeCart->addItem($item)->setUpdated();
                        }

                        $this->em->remove($currentCart);
                        $this->em->persist($activeCart);
                        $session->set('cart_id', $activeCartId);
                        $this->deactivateOtherCart($activeCart);
                    }
                }
            } elseif (!$cartId && $activeCartId) {
                // No session cart, but DB has one
                $activeCart->setActiveStatus(true)->setUpdated();
                $this->em->persist($activeCart);
                $session->set('cart_id', $activeCartId);
            } elseif ($cartId && !$activeCartId) {
                // No session cart, but DB has one
                $activeCart = $this->orderRepo->find($cartId);
                if ($activeCart) {
                    $activeCart->setActiveStatus(true)->setUpdated();
                    $this->em->persist($activeCart);

                    $session->set('cart_id', $activeCartId);
                }
            } elseif (!$cartId && !$activeCartId) {
                // Create new cart
                $activeCart = new Order();
                $activeCart->setStatus(Order::STATUS_CART)
                           ->setUserAgent($userAgent)
                           ->setCreated()
                           ->setUpdated();

                $this->em->persist($activeCart);
                $this->em->flush(); // Need ID now

                $session->set('cart_id', $activeCart->getId());
            }
        } else {
            // Anonymous user
            if ($cartId) {
                $activeCart = $this->orderRepo->checkCart($cartId) ? $this->orderRepo->find($cartId) : null;
            }

            if (!$activeCart) {
                // Create new cart
                $activeCart = new Order();
                $activeCart->setStatus(Order::STATUS_CART)
                           ->setUserAgent($userAgent)
                           ->setCreated()
                           ->setUpdated();

                $this->em->persist($activeCart);
                $this->em->flush(); // Need ID now

                $session->set('cart_id', $activeCart->getId());
            }
        }

        // Final flush to persist all changes
        if ($this->em->getUnitOfWork()->getScheduledEntityInsertions()
            || $this->em->getUnitOfWork()->getScheduledEntityUpdates()
            || $this->em->getUnitOfWork()->getScheduledEntityDeletions()) {
            $this->em->flush();
        }

        $cart = $this->orderRepo->cart($activeCart->getId());
        $this->cart = $cart;
    }

    protected function deactivateOtherCart(?Order $cart)
    {
        $session = $this->requestStack->getSession();
        $session->set('cart_id', $cart->getId());

        if ($this->getUser()) {
            $user_id = $this->getUser()->getId();
            $user = $this->getUser();

            $cartSet = $this->orderRepo->findBy(
                [
                    'users' => $user_id,
                    'status' => Order::STATUS_CART,
                ]
            );

            foreach ($cartSet as $i => $val) {
                if ($cartSet[$i] != $cart) {
                    $cartSet[$i] = $cartSet[$i]->setActiveStatus(false);
                } elseif ($cartSet[$i] == $cart) {
                    $cartSet[$i] = $cartSet[$i]->setActiveStatus(true);
                }

                $this->em->persist($cartSet[$i]);
            }

            $this->em->flush();
        }
    }

    protected function deactivateCart()
    {
        if ($this->getUser()) {
            $user_id = $this->getUser()->getId();
            $user = $this->getUser();

            $cartActiveSet = $this->orderRepo->findBy(
                [
                    'users' => $user_id,
                    'status' => Order::STATUS_CART,
                    'activeStatus' => true,
                ],
                ['updated' => 'ASC']
            );

            // if (empty($cartActiveSet)) return [];

            /* Check: If there are more than one active cart, set one of them
             as inactive. */
            if (2 === count($cartActiveSet)) {
                foreach ($cartActiveSet as $i => $cart) {
                    if (0 === $i) {
                        $activeStatus = $cart->isActiveStatus();
                        if (true == $activeStatus) {
                            $cart->setActiveStatus(false);
                        }

                        $this->em->persist($cart);
                        $this->em->flush();
                    }
                }
            }

            /* Fetch all existing carts. */
            $cartSet = $this->orderRepo->findBy(
                [
                    'users' => $user_id,
                    'status' => Order::STATUS_CART,
                ],
                ['updated' => 'ASC']
            );

            if (1 === count($cartSet)) {
                $activeStatus = $cart->isActiveStatus();
                if (true == $activeStatus) {
                    $cart->setActiveStatus(false);
                } elseif (false == $activeStatus) {
                    $cart->setActiveStatus(true);
                }

                $this->em->persist($cart);
                $this->em->flush();
            }

            if (2 === count($cartSet)) {
                foreach ($cartSet as $i => $cart) {
                    $activeStatus = $cart->isActiveStatus();
                    if (true == $activeStatus) {
                        $cart->setActiveStatus(false);
                    } elseif (false == $activeStatus) {
                        $cart->setActiveStatus(true);
                    }

                    $this->em->persist($cart);
                }
                $this->em->flush();
            }

            $cart = $this->orderRepo->findOneBy(
                [
                    'users' => $user_id,
                    'status' => Order::STATUS_CART,
                    'activeStatus' => true,
                ]
            );

            return $cart;
        }
    }
}
