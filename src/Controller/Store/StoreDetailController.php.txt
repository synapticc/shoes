<?php

// src/Controller/Store/StoreDetailController.php

namespace App\Controller\Store;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Controller\Store\Utils\StoreRecentTrait as Recent;
use App\Entity\Product\Product\Product;
use App\Repository\Billing\OrderItemRepository as OrderItemRepo;
use App\Repository\Billing\OrderRepository as OrderRepo;
use App\Repository\Product\ProductData\ProductDataRepository as ProductDataRepo;
use App\Repository\Review\ReviewRepository as ReviewRepo;
use App\Repository\User\UserRepository as UserRepo;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreDetailController extends AbstractController
{
    use Recent;
    use Cart;
    use Attributes;

    #[ParamDecryptor(['id'])]
    public function show(ProductDataRepo $product, OrderRepo $orderRepo, OrderItemRepo $itemRepo, ReviewRepo $reviewRepo, UserRepo $userRepo, Request $r, string $id): Response
    {
        // If 'id' is absent, redirect to homepage.
        if (empty($id)) {
            return $this->redirectToRoute('store');
        }

        $user = $this->getUser();

        // Retrieve ProductData array
        $pd = $product->fetch($id);
        $p = $pd['productId'];

        // Retrieve reviews
        $reviews = $reviewRepo->productReviews($r, $p);

        // Best review
        $bestReview = $reviewRepo->bestReview($p);

        // Average ratings of review
        $meanRating = $reviewRepo->meanRating($p);

        // Number of reviews
        $countReviews = $reviewRepo->countReviews($p);

        // Check if user has purchased this product
        $checkPurchase = $itemRepo->checkPurchase($p, $user);

        // Check if user has reviewed this product
        $reviewer = $reviewRepo->reviewer($p, $user);

        // Retrieve links of related sizes
        $sizes = $product->size($pd);

        // Retrieve others colors
        $colors = $product->colors($pd);

        // Retrieve similar products
        $similar = $product->similar($pd);

        // Regroup all sizes
        $sizeAll = $this->size($pd);

        // Retrieve cart items and cart product images
        $this->cart($user);

        // Retrieve recently visited products
        $recent = $product->recent($this->recent($pd), $id);

        // Check if product already exists in cart
        $isProductInCart = false;
        if (!empty($this->cart) && !empty($this->cart['items'])) {
            if (array_key_exists($pd['id'], $this->cart['items'])) {
                $isProductInCart = true;
            }
        }

        return $this->render('store/detail.html.twig', [
            'product' => $pd,
            'otherColors' => $colors,
            'sizes' => $sizes,
            'sizeAll' => $sizeAll,
            'reviews' => $reviews,
            'reviewer' => $reviewer,
            'bestReview' => $bestReview,
            'meanRating' => $meanRating,
            'countReviews' => $countReviews,
            'cart' => $this->cart,
            'recentProduct' => !empty($recent) ? $recent : [],
            'similarProduct' => !empty($similar) ? $similar : [],
            'isProductInCart' => $isProductInCart,
            'checkPurchase' => $checkPurchase,
            'get' => $r->query->all(),
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
            'translate' => $this->translate(),
        ]);
    }

    public function userReviews(int $product, int $page, ReviewRepo $reviewRepo, MaxItems $max)
    {
        $page_size = $max->reviews();
        $reviews = $reviewRepo->productReviews($page, $page_size, $product);

        // Check if user has reviewed this product
        $reviewer = '';
        if (!empty($this->getUser())) {
            $reviewer = $reviewRepo->reviewer($product, $this->getUser());
        }

        return $this->render(
            'store/partials/reviews/reviews.html.twig',
            [
                'reviews' => $reviews,
                'product' => $product,
                'reviewer' => $reviewer,
                'sliderFit' => $this->sliderFit(),
                'sliderWidth' => $this->sliderWidth(),
                'sliderComfort' => $this->sliderComfort(),
            ]
        );
    }
}
