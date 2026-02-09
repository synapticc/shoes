<?php

// src/Controller/User/Profile/PurchaseController.php

namespace App\Controller\User\Profile;

use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\Billing\Order;
use App\Repository\Billing\OrderRepository as OrderRepo;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController extends AbstractController
{
    use Cart;

    public function purchases(Request $r, OrderRepo $orderRepo, Paginator $pg): Response
    {
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        // Check if user has logged
        if ($this->getUser()) {
            $user_id = $this->getUser()->getId();

            // Check for existing order with status = Order::STATUS_PAID
            $purchases = $orderRepo->purchases($user_id);

            // Paginate the results
            $page = $r->query->getInt('page', 1);
            $purchases = $pg->paginate($purchases, $page, 12);
        }

        return $this->render('profile/index.html.twig', [
            'purchases' => $purchases,
            'cart' => $this->cart,
        ]);
    }

    #[ParamDecryptor(['order'])]
    public function purchaseList(Request $r, OrderRepo $orderRepo, ?string $order = null, Paginator $pg): Response
    {
        $purchase = $orderRepo->purchase($order);
        $user = $purchase['user'];

        // Check if the user is the buyer.
        if ($this->getUser()->getId() != $user) {
            return $this->redirectToRoute('store');
        }

        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        // Paginate the results
        $page = $r->query->getInt('page', 1);
        $purchase = $pg->paginate($purchase, $page, 12);

        return $this->render('profile/index.html.twig', [
            'purchase' => $purchase,
            'cart' => $this->cart,
        ]);
    }
}
