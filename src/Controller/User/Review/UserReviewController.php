<?php

// src/Controller/User/Review/UserReviewController.php

namespace App\Controller\User\Review;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\Product\Product\Product;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewData;
use App\Entity\Review\ReviewImage;
use App\Entity\Review\ReviewImage2;
use App\Entity\Review\ReviewImage3;
use App\Entity\Review\ReviewImage4;
use App\Form\Review\ReviewForm;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Product\ProductData\ProductData2Repository as Data2Repo;
use App\Repository\Product\ProductData\ProductDataRepository as DataRepo;
use App\Repository\Review\ReviewRepository as ReviewRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile as File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface as Slug;

/**
 * Display, create, edit and delete product reviews.
 * Operates in User profile section.
 */
class UserReviewController extends AbstractController
{
    use Cart;
    use Attributes;

    public function __construct(Slug $slug)
    {
        $this->slug = $slug;
    }

    /**
     * Display all product reviews.
     *
     * Path: /profile/reviews
     */
    public function index(Request $r, ReviewRepo $reviewRepo, Paginator $pg): Response
    {
        $user = $this->getUser();
        $rdSet = $reviewRepo->userReviews($user);

        $page = $r->query->getInt('page', 1);
        $reviews = $pg->paginate($rdSet, $page, 8);

        // Retrieve cart items and cart product images
        $this->cart($user, null);

        return $this->render('profile/index.html.twig', [
            'reviews' => $reviews,
            'cart' => $this->cart,
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
            'translate' => $this->translate(),
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

    /**
     * Create a new product review.
     *
     * Path: /profile/review/{product}/new
     */
    public function new(
        Request $r,
        Product $product,
        Data2Repo $dataRepo,
        ReviewRepo $reviewRepo,
        ItemRepo $itemRepo,
        ORM $em,
    ): Response {
        // Create new review.
        $review = new Review();
        $user = $this->getUser();

        $reviewData = new ReviewData();
        $paidProducts = $itemRepo->paidProducts($product, $user);

        foreach ($paidProducts as $i => $item) {
            $reviewData = new ReviewData();
            $productData = $item->getProductData();
            $color = $productData->getColor();
            $reviewData
              ->setReview($review)
              ->setProduct($productData)
              ->setItems($item)
              ->setColor($color);

            $review->addReviewData($reviewData);
        }
        $review->setUsers($user)
               ->setProduct($product)
               ->setActive(true)
               ->setCreated()
               ->setUpdated();

        $reviewForm = $this->createForm(ReviewForm::class, $review);
        $reviewForm->handleRequest($r);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $review = $reviewForm->getData();

            $image = $reviewForm->get('image')->getData();
            $image2 = $reviewForm->get('image2')->getData();
            $image3 = $reviewForm->get('image3')->getData();
            $image4 = $reviewForm->get('image4')->getData();

            // Handle images.
            if (!empty($image)) {
                $this->uploadImage($review, $image, null);
            }

            if (!empty($image2)) {
                $this->uploadImage($review, $image2, 2);
            }

            if (!empty($image3)) {
                $this->uploadImage($review, $image3, 3);
            }

            if (!empty($image4)) {
                $this->uploadImage($review, $image4, 4);
            }

            $em->persist($review);
            $em->flush();

            return $this->redirectToRoute('review_index', [], Response::HTTP_SEE_OTHER);
        }

        // Retrieve cart items and cart product images.
        $this->cart($user, null);

        $thumbnails = $dataRepo->thumbnailNew($user, $product);

        return $this->render('profile/index.html.twig', [
            'reviewForm' => $reviewForm,
            'cart' => $this->cart,
            'thumbnails' => $thumbnails,
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
            'translate' => $this->translate(),
        ]);
    }

    public function uploadImage(Review $review, ?File $image, ?int $imageNumber)
    {
        $originalName =
          pathinfo(
              (string) $image->getClientOriginalName(),
              PATHINFO_FILENAME
          );

        $safeName = $this->slug->slug($originalName);
        $filename = $image;
        $extension = strtolower((string) $image->guessExtension());
        $safeName = $safeName.'_'.date(time()).'.'.$extension;
        $user = $this->getUser();
        $uuid = $user->getUuid();
        $current_dir_path = getcwd();
        $filesystem = new Filesystem();
        $imageURL = '';

        // Make a new directory & copy new image.
        try {
            // Store a backup of the original image.
            // New path name.
            $new_file_path = $current_dir_path."/uploads/_original/users/$uuid/review/$safeName";

            if (!$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->copy($image, $new_file_path);
                umask($old);
            }

            // Store image to be displayed.
            $imageURL = "uploads/users/$uuid/review/$safeName";

            $new_file_path = $current_dir_path.'/'.$imageURL;

            if (!$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->copy($image, $new_file_path);
                umask($old);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        switch ($imageNumber) {
            case 2:
                $getReviewImage = 'getReviewImage2';
                $setReviewImage = 'setReviewImage2';
                break;
            case 3:
                $getReviewImage = 'getReviewImage3';
                $setReviewImage = 'setReviewImage3';
                break;
            case 4:
                $getReviewImage = 'getReviewImage4';
                $setReviewImage = 'setReviewImage4';
                break;
            default:
                $getReviewImage = 'getReviewImage';
                $setReviewImage = 'setReviewImage';
                break;
        }

        // Create new ReviewImage and set image paths.
        if (!empty($imageURL)) {
            if (empty($review->$getReviewImage())) {
                $reviewImage = match ($imageNumber) {
                    2 => new ReviewImage2(),
                    3 => new ReviewImage3(),
                    4 => new ReviewImage4(),
                    5 => new ReviewImage5(),
                    default => new ReviewImage(),
                };

                $reviewImage->setImage($imageURL);
            }
            // Update existing ProductImage.
            elseif (!empty($review->$getReviewImage())) {
                $reviewImage = $review->$getReviewImage();
                $reviewImage->setImage($imageURL);
            }

            // Assign to parent ProductColor.
            $review->$setReviewImage($reviewImage);
        }

        return $review;
    }

    /**
     * Edit product review.
     *
     * Path: /profile/reviews/{review}/edit
     */
    public function edit(Request $r, Review $review, Data2Repo $dataRepo, ORM $em): Response
    {
        $reviewForm = $this->createForm(ReviewForm::class, $review);
        $reviewForm->handleRequest($r);

        $thumbnails = $dataRepo->thumbnails($review);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $review = $reviewForm->getData();

            $image = $reviewForm->get('image')->getData();
            $image2 = $reviewForm->get('image2')->getData();
            $image3 = $reviewForm->get('image3')->getData();
            $image4 = $reviewForm->get('image4')->getData();

            $deleteImage = $r->request->getBoolean('delete-image');
            $deleteImage2 = $r->request->getBoolean('delete-image2');
            $deleteImage3 = $r->request->getBoolean('delete-image3');
            $deleteImage4 = $r->request->getBoolean('delete-image4');

            // Handle images.
            if (!empty($image)) {
                $this->uploadDeleteImage($review, $image, null, $deleteImage);
            } elseif (true === $deleteImage) {
                $this->deleteImage($review, null);
            }

            if (!empty($image2)) {
                $this->uploadDeleteImage($review, $image2, 2, $deleteImage2);
            } elseif (true === $deleteImage2) {
                $this->deleteImage($review, 2);
            }

            if (!empty($image3)) {
                $this->uploadDeleteImage($review, $image3, 3, $deleteImage3);
            } elseif (true === $deleteImage3) {
                $this->deleteImage($review, 3);
            }

            if (!empty($image4)) {
                $this->uploadDeleteImage($review, $image4, 4, $deleteImag4);
            } elseif (true === $deleteImage4) {
                $this->deleteImage($review, 4);
            }

            $em->persist($review);
            $em->flush();

            if (!empty($r->get('redirect_url'))) {
                /* redirect_url = /Crocs/Classic/EkOTOPvJztdM9iZ9qcAEUpJ9pj3Z
                   $brand = Crocs
                   $name = Classic
                   $product = EkOTOPvJztdM9iZ9qcAEUpJ9pj3Z
                */
                $url = explode('/', $r->get('redirect_url'));
                // dd($url);
                $brand = $url[1];
                $name = $url[2];
                $product = $url[3];

                // Redirect to the same product page
                /*  The pattern [^/]+ will match any sequence of characters that
                  does not include a forward slash, which is useful for
                  validating paths or extracting segments from a URL
                  that are not separated by slashes.
                */
                $pattern = '/[^\/]+/';
                if (preg_match($pattern, $product)) {
                    return $this->redirectToRoute('store_details', [
                        'brand' => $brand,
                        'name' => $name,
                        'id' => $product,
                        '_fragment' => 'ps-review-header']);
                }

                return $this->redirectToRoute('store');
            }

            return $this->redirectToRoute('review_index', [], Response::HTTP_SEE_OTHER);
        }
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        return $this->render('profile/index.html.twig', [
            'review' => $review,
            'reviewForm' => $reviewForm,
            'cart' => $this->cart,
            'thumbnails' => $thumbnails,
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
            'translate' => $this->translate(),
        ]);
    }

    public function deleteImage(Review $review, ?int $imageNumber)
    {
        $filesystem = new Filesystem();
        $current_dir_path = getcwd();

        switch ($imageNumber) {
            case 2:
                $getReviewImage = 'getReviewImage2';
                $setReviewImage = 'setReviewImage2';
                break;
            case 3:
                $getReviewImage = 'getReviewImage3';
                $setReviewImage = 'setReviewImage3';
                break;
            case 4:
                $getReviewImage = 'getReviewImage4';
                $setReviewImage = 'setReviewImage4';
                break;
            default:
                $getReviewImage = 'getReviewImage';
                $setReviewImage = 'setReviewImage';
                break;
        }

        // Delete existing images
        $reviewImage = $review->$getReviewImage();
        $imagePath = $reviewImage->getImage();

        if (!empty($imagePath)) {
            $imageToRemove = [$current_dir_path.'/'.$imagePath];
            try {
                // remove binary images files
                $filesystem->remove($imageToRemove);
            } catch (IOExceptionInterface $exception) {
                echo 'Error deleting directory at'.$exception->getPath();
            }
        }

        // Remove entry in review_image table
        $reviewImage->setImage(null)->setUpdated();
        $review->$getReviewImage($reviewImage);

        return $review;
    }

    public function uploadDeleteImage(Review $review, ?File $image, ?int $imageNumber, ?bool $delete)
    {
        $originalName =
          pathinfo(
              (string) $image->getClientOriginalName(),
              PATHINFO_FILENAME
          );

        $safeName = $this->slug->slug($originalName);
        $filename = $image;
        $extension = strtolower((string) $image->guessExtension());
        $safeName = $safeName.'_'.date(time()).'.'.$extension;
        $user = $this->getUser();
        $uuid = $user->getUuid();
        $current_dir_path = getcwd();
        $filesystem = new Filesystem();
        $imageURL = '';

        // Make a new directory & copy new image.
        try {
            // Store a backup of the original image.
            // New path name.
            $new_file_path = $current_dir_path."/uploads/_original/users/$uuid/review/$safeName";

            if (!$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->copy($image, $new_file_path);
                umask($old);
            }

            // Store image to be displayed.
            $imageURL = "uploads/users/$uuid/review/$safeName";

            $new_file_path = $current_dir_path.'/'.$imageURL;

            if (!$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->copy($image, $new_file_path);
                umask($old);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        switch ($imageNumber) {
            case 2:
                $getReviewImage = 'getReviewImage2';
                $setReviewImage = 'setReviewImage2';
                break;
            case 3:
                $getReviewImage = 'getReviewImage3';
                $setReviewImage = 'setReviewImage3';
                break;
            case 4:
                $getReviewImage = 'getReviewImage4';
                $setReviewImage = 'setReviewImage4';
                break;
            default:
                $getReviewImage = 'getReviewImage';
                $setReviewImage = 'setReviewImage';
                break;
        }

        if (true === $delete or !empty($review->$getReviewImage())) {
            $this->deleteImage($review, $imageNumber);

            $reviewImage = $review->$getReviewImage();
            // Remove entry in review_image table
            $reviewImage->setImage(null)->setUpdated();
            $review->$getReviewImage($reviewImage);
        }

        // Create new ReviewImage and set image paths.
        if (!empty($imageURL)) {
            if (empty($review->$getReviewImage())) {
                $reviewImage = match ($imageNumber) {
                    2 => new ReviewImage2(),
                    3 => new ReviewImage3(),
                    4 => new ReviewImage4(),
                    5 => new ReviewImage5(),
                    default => new ReviewImage(),
                };

                $reviewImage->setImage($imageURL);
            }
            // Update existing ProductImage.
            elseif (!empty($review->$getReviewImage())) {
                $reviewImage = $review->$getReviewImage();
                $reviewImage->setImage($imageURL);
            }

            // Assign to parent Review.
            $review->$setReviewImage($reviewImage);
        }

        return $review;
    }

    /**
     * Delete product review.
     *
     * Path: /profile/review/{review}/delete
     */
    public function delete(Request $r, Review $review, ReviewRepo $reviewRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $r->request->get('_token'))) {
            if (!empty($review->getReviewImage())) {
                $this->deleteImage($review, null);
            }

            if (!empty($review->getReviewImage2())) {
                $this->deleteImage($review, 2);
            }

            if (!empty($review->getReviewImage3())) {
                $this->deleteImage($review, 3);
            }

            if (!empty($review->getReviewImage4())) {
                $this->deleteImage($review, 4);
            }

            $reviewRepo->remove($review);
        }

        return $this->redirectToRoute('review_index', [], Response::HTTP_SEE_OTHER);
    }
}
