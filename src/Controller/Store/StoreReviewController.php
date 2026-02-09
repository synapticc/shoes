<?php

// src/Controller/Store/StoreReviewController.php

namespace App\Controller\Store;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHelpful as Helpful;
use App\Form\Search\SearchForm;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Product\ProductData\ProductDataRepository as DataRepo;
use App\Repository\Review\ReviewHelpfulRepository as HelpfulRepo;
use App\Repository\Review\ReviewRepository as ReviewRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Display, create, edit and delete product reviews.
 * Operates in User profile section.
 */
class StoreReviewController extends AbstractController
{
    use Cart;
    use Attributes;

    /**
     * Display all reviews.
     *
     * Path: /reviews
     */
    public function index(Request $r, ReviewRepo $reviewRepo, Search $search, HelpfulRepo $helpfulRepo, Paginator $pg): Response
    {
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        // Search specific Review.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = $search->search();
        } else {
            $searchTerm = null;
        }

        $reviews = $reviewRepo->storeReviews($q, $search, $this->getUser());
        $reviewWords = $reviewRepo->reviewWords($q, $search);

        $count['brand'] = '';
        $count['category'] = '';
        $count['occasion'] = '';
        $count['type'] = '';
        $count['fabrics'] = '';
        $count['textures'] = '';
        $count['color'] = '';
        $count['rating'] = '';
        $count['fit'] = '';
        $count['width'] = '';
        $count['comfort'] = '';
        $count['uploaded'] = '';
        $count['comment'] = '';
        $count['like'] = '';
        $count['delivery'] = '';
        $count['recommend'] = '';

        /* Determine the count of each filter categories */
        if (!empty($reviews)) {
            $productColors = [];
            $colors = [];
            $productFabrics = [];
            $fabrics = [];
            $productTextures = [];
            $textures = [];
            $rating = [];
            $fit = [];
            $uploaded = [];
            $like = [];
            $delivery = [];
            $recommend = [];
            $comment = [];

            foreach ($reviews as $i => $product) {
                $brands[] = $product['brand'];
                $categories[] = $product['category'];
                $occasions[] = $product['occasion'];
                $types[] = $product['type'];
                $rating[] = $product['rating'];
                $fit[] = $product['fit'];
                $width[] = $product['width'];
                $comfort[] = $product['comfort'];
                $uploaded[] = $product['review_image_count'];
                $comment[] = $product['length_comment'];

                if (null !== $product['like']) {
                    $like[] = $product['like'];
                }
                if (null !== $product['delivery']) {
                    $delivery[] = $product['delivery'];
                }
                if (null !== $product['recommend']) {
                    $recommend[] = $product['recommend'];
                }

                if (!empty($product['color']) and !empty($product['thumbnails'])) {
                    foreach ($product['thumbnails'] as $j => $thumbnail) {
                        $productColors[$i][$j] = $thumbnail['colors_set'];

                        if (!empty($thumbnail['fabrics'])) {
                            $productFabrics[$i][$j] = $thumbnail['fabrics'];
                        }

                        if (!empty($thumbnail['textures'])) {
                            $productTextures[$i][$j] = $thumbnail['textures'];
                        }
                    }
                }
            }

            if (!empty($productColors)) {
                array_walk_recursive($productColors, function ($f) use (&$colors) {
                    $colors[] = $f;
                });
            }

            if (!empty($productFabrics)) {
                array_walk_recursive($productFabrics, function ($f) use (&$fabrics) {
                    $fabrics[] = $f;
                });
            }

            $count['brand'] = array_count_values($brands);
            $count['category'] = array_count_values($categories);
            $count['occasion'] = array_count_values(array_merge(...$occasions));
            $count['type'] = array_count_values($types);
            $count['fabrics'] = array_count_values($fabrics);
            $count['color'] = array_count_values($colors);
            $count['rating'] = array_count_values($rating);
            $count['fit'] = array_count_values($fit);
            $count['width'] = array_count_values($width);
            $count['comfort'] = array_count_values($comfort);
            $count['uploaded'] = array_count_values($uploaded);
            $count['comment'] = array_count_values($comment);

            $likeSet =
              array_map(function ($val) {
                  return $val ? 'true' : 'false';
              }, $like);

            $deliverySet =
              array_map(function ($val) {
                  return $val ? 'true' : 'false';
              }, $delivery);

            $recommendSet =
              array_map(function ($val) {
                  return $val ? 'true' : 'false';
              }, $recommend);

            $count['like'] = array_count_values($likeSet);
            $count['delivery'] = array_count_values($deliverySet);
            $count['recommend'] = array_count_values($recommendSet);
        }

        /* Paginate the results | Start */
        $pages = [15, 25, 50, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($reviews) / $itemsPage);
        $page = $r->query->getInt('page', 1);
        $reviews = $pg->paginate($reviews, $page, 10);

        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        $filter = $r->query->all();

        if (empty($filter['order'])) {
            $filter['order'] = 'updated_desc';
        }

        $countries = countries();

        return $this->render('store/review.html.twig', [
            'reviews' => $reviews,
            'relatedWords' => $reviewWords,
            'reviewer' => '',
            'searchForm' => $searchForm,
            'get' => $filter,
            'pages' => $pages,
            'search' => $searchTerm,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'cart' => $this->cart,
            'brands' => $this->brands(),
            'occasions' => $this->getOccasions(),
            'categories' => $this->getCategory(),
            'types' => $this->getTypes(),
            'sockSizes' => $this->sockSizesRaw(),
            'adultSizes' => $this->adultSizesRaw(),
            'kidSizes' => $this->kidSizesRaw(),
            'sizes' => $this->sizes(),
            'colors' => $this->getColors(),
            'fabrics' => $this->getFabrics(),
            'textures' => $this->getTexture(),
            'tags' => $this->getTag(),
            'brandPair' => $this->brandSet(false),
            'occasionPair' => $this->getOccasionSet(false),
            'typePair' => $this->getTypeSet(false),
            'categoryPair' => $this->getCategorySet(false),
            'colorPair' => $this->getColorSet(false),
            'fabricPair' => $this->getFabricSet(false),
            'texturePair' => $this->getTextureSet(false),
            'sizePair' => $this->sizeSet(),
            'price_range' => $this->getPriceRange(),
            'sorting' => $this->getReviewSorting(),
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
            'thumbnails' => $this->thumbnail(),
            'uploadedSet' => $this->uploaded(),
            'uploadedPair' => $this->uploadedSet(false),
            'commentSet' => $this->comment(),
            'commentPair' => $this->commentSet(false),
            'likeSet' => $this->liked(),
            'likePair' => $this->likedSet(false),
            'deliverySet' => $this->delivery(),
            'deliveryPair' => $this->deliverySet(false),
            'recommendSet' => $this->recommend(),
            'recommendPair' => $this->recommendSet(false),
            'count' => $count,
            'translate' => $this->translate(),
            'countries' => $countries,
            'sample' => $reviewRepo->sample(),
        ]);
    }

    /**
     * Display all products which hasn't been reviewed yet.
     *
     * Path: /profile/new-review
     */
    public function list(Request $r, DataRepo $dataRepo, ItemRepo $itemRepo, Paginator $pg): Response
    {
        $user = $this->getUser();
        $reviewNewSet = $itemRepo->reviews($user);

        $page = $r->query->getInt('page', 1);
        $reviews = $pg->paginate($reviewNewSet, $page, 5);

        // Retrieve cart items and cart product images
        $this->cart($user, null);

        return $this->render('profile/index.html.twig', [
            'reviews' => $reviews,
            'cart' => $this->cart,
        ]);
    }

    public function helpful(RequestStack $stack, ORM $em, int $review, ?int $helpful, ReviewRepo $reviewRepo, HelpfulRepo $helpfulRepo)
    {
        if (!empty($this->getUser())) {
            $user = $this->getUser();
            $unhelpful = false;

            if (empty($helpful)) {
                $helpful = new Helpful();
                $vote = true;
                $helpful
                  ->setIsHelpful($vote)
                  ->setUsers($user)
                  ->setCreated()
                ;

                $review = $reviewRepo->find($review);
                $review->addReviewHelpful($helpful);
                $em->persist($review);
                $em->flush();
                $review = $review->getId();
            } else {
                $updateHelpful = $helpfulRepo->updateHelpful($helpful);
                $updatedRow = $updateHelpful['updatedRow'];
                $unhelpful = $updateHelpful['unhelpful'];

                if (false === $updatedRow) {
                    $helpful = new Helpful();
                    $vote = true;
                    $helpful
                      ->setIsHelpful($vote)
                      ->setUsers($user)
                      ->setCreated()
                    ;

                    $review = $reviewRepo->find($review);
                    $review->addReviewHelpful($helpful);
                    $em->persist($review);
                    $em->flush();
                    $review = $review->getId();
                }
            }

            $helpfulCount = $helpfulRepo->helpfulCount($review);

            $data = ['helpfulCount' => $helpfulCount,
                'unhelpful' => $unhelpful];

            $response = new JsonResponse($data);

            return $response;

            // $timestamps = date_timestamp_get($newCart->getUpdated());

            // $response = new Response();
            // $response->setCharset('ISO-8859-1');
            // $response->headers->set('Content-Type', 'text/plain');
            // $content =
            //   $this->renderView('store/partials/reviews/indicator-yes.html.twig', [
            //     'helpful' => $helpfulCount,
            //     'checkHelpful' => $checkHelpful,
            //     'id' => $review->getId()
            //   ]);
            //
            // $response->setContent($content);
            //
            // return $response;
        }

        return new Response();
    }

    public function unhelpful(RequestStack $stack, ORM $em, Review $review, ReviewRepo $reviewRepo, HelpfulRepo $helpfulRepo)
    {
        if (!empty($this->getUser())) {
            $user = $this->getUser();

            $checkHelpful =
              $helpfulRepo->checkHelpful($review->getId(), $user->getId());

            if (false === $checkHelpful) {
                $helpful = new Helpful();
                $helpful
                  ->setIsHelpful(false)
                  ->setUsers($user)
                  ->setCreated()
                ;
            } else {
                $helpful =
                  $helpfulRepo->findOneBy([
                      'review' => $review,
                      'users' => $user,
                  ]);

                if (null === $helpful->getIsHelpful()
                    or true === $helpful->getIsHelpful()) {
                    $helpful = false;
                } else {
                    $helpful = null;
                }

                $helpful
                  ->setIsHelpful($helpful)
                  ->setUpdated()
                ;
            }

            $review->addReviewHelpful($helpful);
            $em->persist($review);
            $em->flush();

            $helpfulCount = $helpfulRepo->notHelpfulCount($review->getId());

            $response = new Response($helpfulCount);

            return $response;

            // $timestamps = date_timestamp_get($newCart->getUpdated());
            // $response->setCharset('ISO-8859-1');
            // $response->headers->set('Content-Type', 'text/plain');
            // $content =
            //   $this->renderView('store/partials/cart/cart-new.html.twig',[]);
            //
            // $response->setContent($content);
            //
            // return $response;
        }

        return new Response();
    }
}
