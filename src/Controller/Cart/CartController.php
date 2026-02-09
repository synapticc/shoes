<?php

// src/Controller/Cart/CartController.php

namespace App\Controller\Cart;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\Billing\Billing;
use App\Entity\Billing\Order;
use App\Entity\Billing\OrderItem;
use App\Entity\Product\ProductData\ProductData;
use App\Form\Billing\BillingForm;
use App\Form\Billing\OrderForm;
use App\Form\Billing\Transfer\CartItemTransferForm;
use App\Form\Billing\Transfer\OrderTransferForm;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Billing\OrderRepository as OrderRepo;
use App\Repository\Product\ProductData\ProductData2Repository as DataRepo;
use DateTime;
use Doctrine\ORM\EntityManagerInterface as ORM;
use libphonenumber;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CartController extends AbstractController
{
    use Cart;
    use Attributes;

    private $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /* - Fetch cart and cart item using Order and OrderItem array.
       - Render each form separately.
        Each OrderTransferForm renders a single Order without any children.
        Each OrderItemTransferForm renders a single OrderItem without any parent.
        This disassembled method loads faster and requires less ORM cache.
    */
    public function index(OrderRepo $orderRepo): Response
    {
        $user_id = $this->getUser()->getId();

        $this->cart($this->getUser(), null);

        $cartList = $orderRepo->fetchCart($user_id);

        $cartTray = [];
        foreach ($cartList as $i => $item) {
            if (true === $item['activeStatus']) {
                $cartTray = $item;
            }
        }

        return $this->render('store/cart.html.twig', [
            'cartList' => $cartList,
            'cart' => $cartTray,
            // 'deviceInfo' => $this->deviceInfo,
        ]);
    }

    #[ParamDecryptor(['paidOrder'])]
    public function billing(Request $request, Order $paidOrder, OrderRepo $orderRepo, ItemRepo $itemRepo, RequestStack $requestStack, ORM $em, Encryptor $encryptor): Response
    {
        // Validation: Redirect to Cart page if cart is empty
        if ($paidOrder->getItems()->isEmpty()) {
            return $this->redirectToRoute('cart');
        }

        // Validation: Redirect to Store page if cart is paid
        if ('paid' == $paidOrder->getStatus()) {
            return $this->redirectToRoute('store');
        }

        // Assign logged user to variable $user
        $user = $this->getUser();
        $user_id = $this->getUser()->getId();

        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $phone = $user->getUserPhone();
        $mobile = $phone->getMobile();
        $landline = $phone->getLandline();
        $address = $user->getUserAddress();
        $street = $address->getStreet();
        $city = $address->getCity();
        $zip = $address->getZip();
        $country = $address->getCountry();

        /* Before sending to billing page, first check
           if user profile has first name and last name
           filled out. */
        if (empty($firstName) || empty($lastName)
            || empty($mobile) || empty($landline)
            || empty($street) || empty($city)
            || empty($zip) || empty($country)) {
            return $this->redirectToRoute(
                'user_profile',
                [
                    'billing' => true,
                    'id' => $this->encryptor->encrypt($paidOrder->getId()),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        $billing = new Billing();
        $billingForm = $this->createForm(BillingForm::class, $billing);
        $billingForm->handleRequest($request);

        $countries = countries();
        foreach ($countries as $key => $country) {
            $phoneCodes[$country['calling_code']] = $country['iso_3166_1_alpha2'];
        }

        // Retrieve cart items and cart product images
        $this->cart($user, null);

        if ($billingForm->isSubmitted() && $billingForm->isValid()) {
            $billing = $billingForm->getData();

            /* Validate mobile and phone separately using the PHP 'libphonenumber' library. */
            $validator = libphonenumber\PhoneNumberUtil::getInstance();
            $mobile = $billing->getMobile();
            $isMobileValid = $validator->isValidNumber($mobile);

            $landline = $billing->getLandline();
            $isLandlineValid = true;

            if (!empty($landline)) {
                $isLandlineValid = $validator->isValidNumber($landline);
            }

            if (false === $isLandlineValid || false === $isMobileValid) {
                /* If landline is invalid, flash 'Invalid landline number.' error message */
                if (false === $isLandlineValid) {
                    $this->addFlash('landline_error', 'Invalid landline number.');
                }

                /* If mobile is invalid, flash 'Invalid mobile number.' error message */
                if (false === $isMobileValid) {
                    $this->addFlash('mobile_error', 'Invalid mobile number.');
                }

                return $this->redirectToRoute('store_billing', [
                    'billing' => $this->encryptor->encrypt($paidOrder->getId()),
                ], Response::HTTP_SEE_OTHER);
            }

            $items = $itemRepo->findBy(['orderRef' => $paidOrder->getId()]);

            /* Retrieve productData & quantity from order.
               Substract quantity from product table.
            */
            if (!empty($items)) {
                for ($i = 0; $i < \count($items); ++$i) {
                    $productData[$i] = $items[$i]->getProductData();
                    $qtyByCart[$i] = $items[$i]->getQuantity();

                    $qtyInStock[$i] = $productData[$i]->getQtyInStock();
                    $updatedQty[$i] = $qtyInStock[$i] - $qtyByCart[$i];

                    $decreasedProduct[$i] = $productData[$i]->setQtyInStock($updatedQty[$i]);
                    $em->persist($decreasedProduct[$i]);
                }
            }

            // Replace white space in card number
            $card = preg_replace('/\s+/', '', $billing->getCardNumber());
            $invoiceTotal = $paidOrder->getTotal();

            $billing->setCardNumber($card)
                    ->setInvoiceTotal($invoiceTotal);

            $paidOrder
              ->setStatus(Order::STATUS_PAID)
              ->setActiveStatus(false)
              ->setUpdated();

            $paidOrder->setBilling($billing);
            // dd($paidOrder);
            $em->persist($paidOrder);
            $em->flush();

            return $this->redirectToRoute('invoice_confirm', [
                'invoice' => $this->encryptor->encrypt($paidOrder->getId()),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('store/checkout.html.twig', [
            'cart' => $this->cart,
            'billing' => $paidOrder,
            'billingForm' => $billingForm,
        ]);
    }

    public function add(RequestStack $requestStack, ORM $em, OrderRepo $orderRepo, ItemRepo $itemRepo, ProductData $productData)
    {
        $session = $requestStack->getSession();
        $request = $requestStack->getCurrentRequest();
        $userAgent = $request->headers->get('user-agent');

        $qty = 1;
        if ($request->query->has('qty')) {
            $qty = (int) $request->query->get('qty');

            // Minimum value of quantity should be 1.
            if ($qty < 1 or '' == $qty or 0 == $qty) {
                $qty = 1;
            }

            $maxQty = (int) $productData->getQtyInStock();

            // Maximum value of quantity should be the quantity available.
            if ($qty > $maxQty) {
                $qty = $maxQty;
            }
        }

        // Retrieve existing cart
        if (!empty($session->get('cart_id'))) {
            $cart_id = $session->get('cart_id');
            $currentCart = $orderRepo->find($cart_id);
        }
        // Create new cart if absent
        elseif (empty($session->get('cart_id'))) {
            $newCart = new Order();
            $newCart->setStatus(Order::STATUS_CART)
                    ->setCreated()
                    ->setActiveStatus(true)
                    ->setUserAgent($userAgent)
                    ->setUpdated();

            // Check if user has logged
            if (!empty($this->getUser())) {
                $newCart->setUsers($this->getUser());
            }

            $em->persist($newCart);
            $em->flush();
            $this->deactivateOtherCart($newCart);

            $currentCart = $newCart;
            $session->set('cart_id', $currentCart->getId());
        }

        // Check if user has logged
        if (!empty($this->getUser())) {
            $user_id = $this->getUser()->getId();
            $user = $this->getUser();

            if (empty($session->get('cart_id'))) {
                $currentCart = $orderRepo->findOneBy([
                    'users' => $user_id,
                    'status' => Order::STATUS_CART,
                    'activeStatus' => true,
                ]);
            }

            // update existing orders for logged user
            if (!empty($currentCart)) {
                // Check if the current product already exists
                $existingItem =
                  $itemRepo->findOneBy([
                      'orderRef' => $currentCart,
                      'product' => $productData,
                  ]);

                if (!empty($existingItem)) {
                    $item = $existingItem;
                    $existingQty = (int) $item->getQuantity();
                    $updatedQty = $existingQty + $qty;
                    // Update item
                    $item->setQuantity($updatedQty)
                         ->setUpdated();
                } else {
                    $item = new OrderItem();
                    $item->setQuantity($qty)
                         ->setOrderRef($currentCart)
                         ->setProductData($productData)
                         ->setCreated()
                         ->setUpdated();
                }

                // Update cart
                $updatedOrder = $currentCart->addItem($item)->setUpdated();
                $em->persist($updatedOrder);
                $em->flush();

                /* Add 'updated' timestamps (as data attribute) alongside
                  the updated cart items. */
                $cart = $orderRepo->cart($updatedOrder->getId());
                $response = new Response();
                $timestamps = date_timestamp_get($updatedOrder->getUpdated());

                // /* Delete any previous Cart cookie.*/
                // if ($request->cookies->has(Order::COOKIE_CART))
                //   $response->headers->clearCookie(Order::COOKIE_CART);
                //
                // /* Create cart cookie.*/
                // $cookie = Cookie::create(Order::COOKIE_CART)
                //             ->withValue($timestamps)
                //             ->withExpires((new \DateTime('+1 day'))
                //             ->format('U'))
                //             ->withSecure(false)
                //             ->withHttpOnly(false)
                //             ;
                // $response->headers->setCookie($cookie);
                $response->setCharset('ISO-8859-1');
                $response->headers->set('Content-Type', 'text/plain');
                $content =
                  $this->renderView(
                      'store/partials/cart/cart-top.html.twig',
                      ['cart' => $cart]
                  );

                $response->setContent($content);

                return $response;

                // $cart = $orderRepo->cart($currentCart->getId());
                // return $this->render(
                //     'store/partials/cart/cart-top.html.twig',
                //     [ 'cart' => $cart ]);
            }
            // create new order for logged user
            elseif (empty($currentCart)) {
                if ($request->query->get('qty')) {
                    $newOrder = new Order();
                    $item = new OrderItem();
                    // assign product to item table
                    $item->setProductData($productData)
                         ->setCreated();

                    $qty = $request->query->get('qty');

                    // assign Product to Item
                    $item->setQuantity($qty)
                         ->setOrderRef($currentCart)
                         ->setProductData($productData)
                         ->setCreated()
                         ->setUpdated();

                    // assign Item to Order
                    $newOrder->addItem($item)
                             ->setUserAgent($userAgent)
                             ->setStatus(Order::STATUS_CART)
                             ->setUsers($user)
                             ->setActiveStatus(true)
                             ->setCreated()
                             ->setUpdated();

                    // assign item to order table
                    $em->persist($newOrder);
                    $em->flush();

                    $this->deactivateOtherCart($newOrder);
                }

                $cart = $orderRepo->cart($newOrder->getId());

                return $this->render(
                    'store/partials/cart/cart-top.html.twig',
                    ['cart' => $cart]
                );
            }
        }
        // Anonymous users
        elseif (empty($this->getUser())) {
            // create new order
            if (!empty($session)) {
                // import existing cart / order from session
                if (!empty($session->get('cart_id'))) {
                    $cart_id = $session->get('cart_id');

                    // retrieve existing cart
                    $existingCart = $orderRepo->find($cart_id);

                    if (empty($existingCart)) {
                        return false;
                    }

                    // Check if the current product already exists
                    $existingItem =
                      $itemRepo->findOneBy([
                          'orderRef' => $currentCart,
                          'product' => $productData,
                      ]);

                    if (!empty($existingItem)) {
                        $item = $existingItem;
                        $existingQty = (int) $item->getQuantity();
                        $updatedQty = $existingQty + $qty;
                        // Update item
                        $item->setQuantity($updatedQty)
                             ->setUpdated();
                    } else {
                        $item = new OrderItem();
                        // assign product to item table
                        $item->setProductData($productData)
                             ->setQuantity($qty)
                             ->setCreated()
                             ->setUpdated();
                    }

                    // assign item to order table
                    $updatedCart =
                    $existingCart->addItem($item)
                                 ->setUserAgent($userAgent)
                                 ->setUpdated();

                    $em->persist($updatedCart);
                    $em->flush();

                    $cart = $orderRepo->cart($cart_id);

                    return $this->render(
                        'store/partials/cart/cart-top.html.twig',
                        ['cart' => $cart]
                    );
                }
                // new cart / new order
                elseif (empty($session->get('cart_id'))) {
                    // creating new order
                    $newOrder = new Order();
                    $item = new OrderItem();

                    // assign product to item table
                    $item->setProductData($productData)
                         ->setQuantity($qty)
                         ->setCreated()
                         ->setUpdated();

                    // assign item to order table
                    $newOrder->addItem($item)
                             ->setUserAgent($userAgent)
                             ->setStatus(Order::STATUS_CART)
                             ->setActiveStatus(true)
                             ->setUpdated()
                             ->setCreated();

                    $em->persist($newOrder);
                    $em->flush();

                    // replicate new order_id into session
                    $session->set('cart_id', $newOrder->getId());

                    $cart = $orderRepo->cart($newOrder->getId());

                    return $this->render(
                        'store/partials/cart/cart-top.html.twig',
                        ['cart' => $cart]
                    );
                }
            }
        }

        return new Response();
    }

    public function item(RequestStack $requestStack, ORM $em, OrderItem $item, OrderRepo $orderRepo)
    {
        $request = $requestStack->getCurrentRequest();

        // Check if user has logged
        if (!empty($this->getUser())) {
            $qty = 1;
            if ($request->query->has('quantity')) {
                $qty = (int) $request->query->get('quantity');

                // Minimum value of quantity should be 1.
                if ($qty < 1 or '' === $qty or 0 === $qty or 'NaN' === $qty
                    or null === $qty or 'undefined' === $qty) {
                    $qty = 1;
                }

                $maxQty = (int) $item->getProductData()->getQtyInStock();

                // Maximum value of quantity should be the quantity available.
                if ($qty > $maxQty) {
                    $qty = $maxQty;
                }

                $item->setQuantity($qty)
                     ->setCreated()->setUpdated();

                try {
                    $em->persist($item);
                    $em->flush();

                    $cart_id = $item->getOrderRef()->getId();
                    $cart = $orderRepo->cart($cart_id);
                    $topCart = $this->renderView(
                        'store/partials/cart/cart-top.html.twig',
                        ['cart' => $cart]
                    );

                    $total = $orderRepo->total($cart_id)['total'];

                    return $this->json([
                        'top' => $topCart,
                        'total' => $total,
                    ]);
                } catch (\Exception $e) {
                    return new Response('Failed to save.');
                }
            } else {
                return new Response('Failed to save.');
            }
        }
    }

    public function newCart(RequestStack $stack, ORM $em, OrderRepo $orderRepo)
    {
        if (!empty($this->getUser())) {
            $user = $this->getUser();
            $existing = $orderRepo->findBy(
                [
                    'users' => $user,
                    'status' => Order::STATUS_CART,
                ]
            );

            if (count($existing) >= 2) {
                return new Response();
            }

            $session = $stack->getSession();
            $r = $stack->getCurrentRequest();
            $userAgent = $r->headers->get('user-agent');

            // Create new cart for cart page
            $newCart = new Order();
            $newCart->setStatus(Order::STATUS_CART)
                    ->setCreated()
                    ->setUpdated()
                    ->setUsers($user)
                    ->setActiveStatus(true)
                    ->setUserAgent($userAgent);

            $em->persist($newCart);
            $em->flush();

            $this->deactivateOtherCart($newCart);
            $session->set('cart_id', $newCart->getId());

            // $cart = $orderRepo->cart($newCart->getId());
            $response = new Response();
            $timestamps = date_timestamp_get($newCart->getUpdated());

            /* Delete any previous Cart cookie. */
            if ($r->cookies->has(Order::COOKIE_CART)) {
                $response->headers->clearCookie(Order::COOKIE_CART);
            }

            /* Create cart cookie. */
            $cookie = Cookie::create(Order::COOKIE_CART)
                        ->withValue($timestamps)
                        ->withExpires((new \DateTime('+1 day'))
                        ->format('U'))
                        ->withSecure(false)
                        ->withHttpOnly(false)
            ;
            $response->headers->setCookie($cookie);
            $response->setCharset('ISO-8859-1');
            $response->headers->set('Content-Type', 'text/plain');
            $content =
              $this->renderView(
                  'store/partials/cart/cart-new.html.twig',
                  ['cart_id' => $newCart->getId()]
              );

            $response->setContent($content);

            return $response;
        }
    }

    public function deactivate(RequestStack $stack, ORM $em, OrderRepo $orderRepo): JsonResponse
    {
        // Check if user has logged
        if (!empty($this->getUser())) {
            $cart = $this->deactivateCart();

            $empty = $this->renderView('store/partials/cart/empty-top.html.twig');
            if (empty($cart)) {
                return $this->json($empty);
            }

            $session = $stack->getSession();
            $session->set('cart_id', $cart->getId());
            $cart = $orderRepo->cart($cart->getId());

            $topCart = $this->renderView(
                'store/partials/cart/cart-top.html.twig',
                ['cart' => $cart]
            );

            return $this->json($topCart);
        }
    }

    public function remove(OrderItem $item, int $qty, RequestStack $requestStack, ORM $em, OrderRepo $orderRepo, ItemRepo $itemRepo, DataRepo $repo)
    {
        // Check if user has logged
        if (!empty($this->getUser())) {
            $cart = $item->getOrderRef();
            $cart_id = $cart->getId();
            $product = $item->getProductData();

            $r = $requestStack->getCurrentRequest();
            $session = $requestStack->getSession();
            if (!empty($session->get('cart_id'))) {
                $session->set('cart_id', $cart_id);
            }

            // Remove cart item
            $em->remove($item);

            // Update cart
            $updatedCart = $cart->setUpdated();
            $timestamps = date_timestamp_get($updatedCart->getUpdated());
            $em->persist($updatedCart);
            $em->flush();

            $product = $repo->cartItem($product->getId());
            $twig = 'store/partials/cart/item-removed.html.twig';
            $cart =
            [
                'product' => $product,
                'cart' => [
                    'id' => $cart_id,
                    'quantity' => $qty],
            ];

            $totalQty = $orderRepo->totalItems($cart_id);

            return $this->json([
                'item' => $this->renderView($twig, $cart),
                'count' => $totalQty,
            ]);
        }
    }

    public function retrieve(RequestStack $requestStack, ORM $em, OrderRepo $orderRepo, ItemRepo $itemRepo, Order $cart, ProductData $product, int $qty)
    {
        // Minimum value of quantity should be 1.
        if ($qty < 1 or '' == $qty or 0 == $qty) {
            $qty = 1;
        }

        // Check if user has logged
        if (!empty($this->getUser())) {
            $item = new OrderItem();

            // Create and add cart item to existing Order (cart)
            $item->setOrderRef($cart)
                 ->setProductData($product)
                 ->setQuantity($qty)
                 ->setCreated()
                 ->setUpdated();

            $itemRepo->add($item);

            // Retrieve cart items and cart product images
            $cartItem = $itemRepo->fetch($item->getId());

            $cart_id = $cart->getId();
            $cart = $orderRepo->cart($cart_id);
            $total = $orderRepo->total($cart_id)['total'];

            $undo = 'store/partials/cart/item-undo.html.twig';
            $top = 'store/partials/cart/cart-top.html.twig';

            return $this->json([
                'item' => $this->renderView($undo, ['product' => $cartItem]),
                'top' => $this->renderView($top, ['cart' => $cart]),
                'count' => $cart['totalItems'],
                'total' => $total,
            ]);
        }
    }

    public function update(RequestStack $requestStack, ORM $em, OrderRepo $orderRepo, ItemRepo $itemRepo): JsonResponse
    {
        $session = $requestStack->getSession();
        $request = $requestStack->getCurrentRequest();
        $cart = [];

        if (!empty($session->get('cart_id'))) {
            $cart_id = $session->get('cart_id');

            // $currentCart = $orderRepo->currentCart($cart_id);
            //
            $cart = $orderRepo->cart($cart_id);

            /* The following response will be sent via AJAX,
               hence the JSON format. */
            return $this->json(
                $this->renderView(
                    'store/partials/cart/cart-top.html.twig',
                    ['cart' => $cart]
                )
            );
        }

        return $this->json(
            $this->renderView('store/partials/cart/empty-cart.html.twig')
        );
    }

    public function clear(Order $cart, ORM $em, OrderRepo $orderRepo)
    {
        // Check if user has logged
        if (!empty($this->getUser())) {
            $cart->removeItems();
            $em->persist($cart);
            $em->flush();

            $cart = $orderRepo->cart($cart->getId());

            return $this->json(
                $this->renderView(
                    'store/partials/cart/cart-top.html.twig',
                    ['cart' => $cart]
                )
            );
        }
    }

    public function delete(Order $cart, RequestStack $requestStack, ORM $em, OrderRepo $orderRepo)
    {
        // Check if user has logged
        if (!empty($this->getUser())) {
            $user = $this->getUser();

            // Remove cart item
            $em->remove($cart);
            $em->flush();

            $newCart = $orderRepo->findOneBy(
                [
                    'users' => $user,
                    'status' => Order::STATUS_CART,
                ]
            );

            if (empty($newCart)) {
                $newCart = new Order();
                $request = $requestStack->getCurrentRequest();
                $userAgent = $request->headers->get('user-agent');
                $newCart
                  ->setStatus(Order::STATUS_CART)
                  ->setUserAgent($userAgent)
                  ->setUsers($user)
                  ->setCreated()
                  ->setUpdated();
            }
            $newCart->setActiveStatus(true);
            $em->persist($newCart);
            $em->flush();

            $session = $requestStack->getSession();
            if (!empty($session->get('cart_id'))) {
                $session->set('cart_id', $newCart->getId());
            }

            $cart = $orderRepo->cart($newCart->getId());

            return $this->json(
                $this->renderView(
                    'store/partials/cart/cart-top.html.twig',
                    ['cart' => $newCart]
                )
            );
        }
    }

    public function latest(RequestStack $request, OrderRepo $orderRepo): JsonResponse
    {
        $session = $request->getSession();
        if (!empty($session->get('cart_id'))) {
            $cart = $session->get('cart_id');
            $latestUpdate = $orderRepo->latest($cart);

            return new JsonResponse($latestUpdate);
        }

        return new JsonResponse(null);
    }

    public function total(Order $cart, RequestStack $request, OrderRepo $orderRepo): JsonResponse
    {
        $total = $cart->getTotal();

        return new JsonResponse($total);
    }

    public function list(RequestStack $requestStack, ORM $em, OrderRepo $orderRepo, ItemRepo $itemRepo): JsonResponse
    {
        // Check if user has logged
        if (!empty($this->getUser())) {
            $session = $requestStack->getSession();
            $request = $requestStack->getCurrentRequest();

            if (!empty($session->get('cart_id'))) {
                $cart_id = $session->get('cart_id');
                $user = $this->getUser()->getId();
                $cart = $orderRepo->cartList($cart_id, $user);

                $list = 'store/partials/cart/cart-list.html.twig';
                $top = 'store/partials/cart/cart-top.html.twig';

                return $this->json([
                    'id' => "tbody-$cart_id",
                    'tbody' => $this->renderView($list, ['cart' => $cart]),
                    'topCart' => $this->renderView($top, ['cart' => $cart]),
                    'count' => $cart['totalItems'],
                ]);
            }

            return $this->json('empty');
        }
    }

    public function createCookie(Order $cart): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $response = new Response();
        $timestamps = date_timestamp_get($cart->getUpdated());

        /* Delete any previous Cart cookie. */
        if ($request->cookies->has(Order::COOKIE_CART)) {
            $response->headers->clearCookie(Order::COOKIE_CART);
        }

        /* Create cart cookie. */
        $cookie = Cookie::create(Order::COOKIE_CART)
                    ->withValue($timestamps)
                    ->withExpires((new \DateTime('+1 day'))->format('U'))
                    ->withSecure(false)
                    ->withHttpOnly(false)
        ;

        $response->headers->setCookie($cookie);

        return $response;
    }

    /* - Fetch cart and cart item using whole Order and OrderItem objects.
       - Render form using embedded children. Each OrderForm
        embeds all the items saved in the cart via OrderItem.
        This bulky form requires more loading time and ORM cache.
    */

    // public function index(
    //     Request $request,
    //     FormFactoryInterface $formFactory,
    //     OrderRepo $orderRepo,
    //     OrderItemRepository $orderItemRepo,
    //     PDRepo $productDataRepo,
    //     EntityManagerInterface $em,
    //     RequestStack $requestStack ): Response
    // {
    //
    //   // handling session
    //   $session = $requestStack->getSession();
    //
    //   // Retrieve cart items and cart product images
    //   $this->cart( $this->getUser(), null);
    //
    //   $user = $this->getUser();
    //   $user_id = $this->getUser()->getId();
    //   $userAgent = $request->headers->get('user-agent');
    //
    //   // Check if user has logged
    //   if ($this->getUser())
    //   {
    //     /* Check for existing order with status = Order::STATUS_CART
    //      Retrieve 'activeStatus = true' first
    //     */
    //     $cart = $orderRepo->findBy([ 'users' => $user_id,
    //                                   'status' => Order::STATUS_CART]);
    //
    //     // update orders for logged user
    //     if (!empty($cart))
    //     {
    //       for ($i=0; $i < count($cart) ; $i++)
    //       {
    //         $cartForm[$i] = $formFactory->createNamed(
    //                             'order_'.$i,
    //                             OrderForm::class,
    //                             $cart[$i]);
    //
    //         $cartForm[$i]->handleRequest($request);
    //
    //         if ($cartForm[$i]->isSubmitted() && $cartForm[$i]->isValid())
    //         {
    //           $cartData[$i] = $cartForm[$i]->getData();
    //
    //           /* Delete cart and empty cart items if 'Delete'
    //            is clicked
    //           */
    //           if ($cartForm[$i]->get('delete')->isClicked())
    //           {
    //             $cartToDelete = $cartData[$i];
    //             $cartToDelete->removeItems();
    //             $em->remove($cartToDelete);
    //             $em->flush();
    //
    //             $remainingCart =
    //               $this->orderRepo->findBy([
    //                   'users' => $user_id,
    //                   'status' => Order::STATUS_CART
    //                ]);
    //
    //             // If it's the only cart left, set active status to true
    //             if (\count($remainingCart) == 1)
    //             {
    //               $remainingCart[0] = $remainingCart[0]->setActiveStatus(true);
    //               $em->persist($remainingCart[0]);
    //             }
    //
    //             $em->flush();
    //             return $this->redirectToRoute('cart');
    //           }
    //
    //           $updatedCart = $cartData[$i]->setUserAgent($userAgent)
    //                                       ->setUpdated(new DateTime());
    //
    //           $em->persist($updatedCart);
    //
    //           if (!empty($session->get('cart_id')))
    //             $session->set('cart_id', $updatedCart->getId() );
    //
    //           for ($j=0; $j < \count($cart) ; $j++)
    //           {
    //             if ($cart[$j] != $updatedCart )
    //             {
    //               if ($updatedCart->isActiveStatus() == true)
    //               {
    //                 $otherCart =  $cart[$j];
    //                 $otherCart->setActiveStatus(false);
    //                 $em->persist($otherCart);
    //               }
    //               elseif ($updatedCart->isActiveStatus() == false)
    //               {
    //                 $otherCart =  $cart[$j];
    //                 $otherCart->setActiveStatus(true);
    //                 $em->persist($otherCart);
    //               }
    //             }
    //           }
    //
    //           // If it's the only cart left, set active status to True
    //           if (\count($cart) == 1)
    //           {
    //             $cart[0] = $cart[0]->setActiveStatus(true);
    //             $em->persist($cart[0]);
    //           }
    //
    //           $em->flush();
    //           return $this->redirectToRoute('cart');
    //         }
    //       }
    //     }
    //     // return empty cart if no order is found for logged user
    //     else
    //     {
    //       $cartForm = $this->createForm(OrderForm::class);
    //     }
    //   }
    //   elseif (empty($this->getUser()))
    //   {
    //     $cartForm = $this->createForm(OrderForm::class);
    //   }
    //
    //   return $this->render('store/cart.html.twig', [
    //       'cartFormObject' => $cartForm,
    //       'cart' => $this->cart,
    //       'deviceInfo' => $this->deviceInfo,
    //   ]);
    // }

    /* - Fetch cart and cart item using Data Transfer Objects (DTO)
         OrderTransfer and OrderItemTransfer.
       - Render each form separately.
        Each OrderTransferForm renders a single Order without any children.
        Each OrderItemTransferForm renders a single OrderItem without any parent.
        This disassembled method requires less loading time and ORM cache.
    */

    // public function index(
    //     Request $request,
    //     FormFactoryInterface $formFactory,
    //     OrderRepo $orderRepo,
    //     OrderItemRepository $orderItemRepo,
    //     PDRepo $productDataRepo,
    //     EntityManagerInterface $em,
    //     RequestStack $requestStack ): Response
    // {
    //   $session = $requestStack->getSession();
    //
    //   // Retrieve cart items and cart product images
    //   $this->cart( $this->getUser(), null);
    //
    //   $user = $this->getUser();
    //   $user_id = $this->getUser()->getId();
    //   $userAgent = $request->headers->get('user-agent');
    //
    //   // Check if user has logged
    //   if ($this->getUser())
    //   {
    //     /* Check for existing order with status = Order::STATUS_CART
    //      Retrieve 'activeStatus = true' first
    //     */
    //     $cart = $orderRepo->cartTransfer($user_id);
    //     $cartItem = $orderItemRepo->cartTransfer($user_id);    //
    //
    //     // update orders for logged user
    //     if (!empty($cart))
    //     {
    //       for ($i=0; $i < count($cart) ; $i++)
    //       {
    //         $cartForm[$i] = $formFactory->createNamed(
    //                             'order_'.$i,
    //                             OrderTransferForm::class,
    //                             $cart[$i]);
    //
    //         $cartForm[$i]->handleRequest($request);
    //
    //         if ($cartForm[$i]->isSubmitted() && $cartForm[$i]->isValid())
    //         {
    //           $cartData[$i] = $cartForm[$i]->getData();
    //           /* Delete cart and empty cart items if 'Delete'
    //            is clicked
    //           */
    //           if ($cartForm[$i]->get('delete')->isClicked())
    //           {
    //             $cartToDelete = $cartData[$i];
    //             $cartToDelete->removeItems();
    //             $em->remove($cartToDelete);
    //             $em->flush();
    //
    //             $remainingCart =
    //               $this->orderRepo->findBy([
    //                   'users' => $user_id,
    //                   'status' => Order::STATUS_CART
    //                ]);
    //
    //             // If it's the only cart left, set active status to true
    //             if (\count($remainingCart) == 1)
    //             {
    //               $remainingCart[0] = $remainingCart[0]->setActiveStatus(true);
    //               $em->persist($remainingCart[0]);
    //             }
    //
    //             $em->flush();
    //             return $this->redirectToRoute('cart');
    //           }
    //
    //           $updatedCart = $cartData[$i]->setUserAgent($userAgent)
    //                                       ->setUpdated(new DateTime());
    //
    //           $em->persist($updatedCart);
    //
    //           if (!empty($session->get('cart_id')))
    //             $session->set('cart_id', $updatedCart->getId() );
    //
    //           for ($j=0; $j < \count($cart) ; $j++)
    //           {
    //             if ($cart[$j] != $updatedCart )
    //             {
    //               if ($updatedCart->isActiveStatus() == true)
    //               {
    //                 $otherCart =  $cart[$j];
    //                 $otherCart->setActiveStatus(false);
    //                 $em->persist($otherCart);
    //               }
    //               elseif ($updatedCart->isActiveStatus() == false)
    //               {
    //                 $otherCart =  $cart[$j];
    //                 $otherCart->setActiveStatus(true);
    //                 $em->persist($otherCart);
    //               }
    //             }
    //           }
    //
    //           // If it's the only cart left, set active status to True
    //           if (\count($cart) == 1)
    //           {
    //             $cart[0] = $cart[0]->setActiveStatus(true);
    //             $em->persist($cart[0]);
    //           }
    //
    //           $em->flush();
    //           return $this->redirectToRoute('cart');
    //         }
    //       }
    //     }
    //     // return empty cart if no order is found for logged user
    //   //   else
    //   //   {
    //   //     $cartForm = $this->createForm(OrderTransferForm::class);
    //   //   }
    //   // }
    //   elseif (empty($this->getUser()))
    //   {
    //     $cartForm = $this->createForm(OrderTransferForm::class);
    //   }
    //
    //
    //   }
    //
    //   if (!empty($cartItem))
    //   {
    //     foreach ($cartItem as $i => $value)
    //     {
    //       $cartItemForm[$i] = $formFactory->createNamed(
    //                           'cart_'.$i,
    //                           CartItemTransferForm::class,
    //                           $cartItem[$i]);
    //
    //       $cartItemForm[$i]->handleRequest($request);
    //
    //       if ($cartItemForm[$i]->isSubmitted()
    //           // && $cartItemForm[$i]->isValid()
    //           )
    //       {
    //         $cartItemData = $cartItemForm[$i]->getData();
    //         $qty = $cartItemData->getQuantity();
    //
    //         $cartItem = $orderItemRepo->find($cartItemData->getId());
    //         $cartItem->setQuantity($qty);
    //
    //         $em->persist($cartItem);
    //         $em->flush();
    //
    //         // $orderItemRepo->add($cartItem, tru);
    //         return $this->redirectToRoute('cart');
    //       }
    //     }
    //   }
    //   return $this->render('store/cart.html.twig', [
    //       'cartFormObject' => $cartForm,
    //       'cartItemFormObject' => $cartItemForm,
    //       'cart' => $this->cart,
    //       'deviceInfo' => $this->deviceInfo,
    //   ]);
    // }
}
