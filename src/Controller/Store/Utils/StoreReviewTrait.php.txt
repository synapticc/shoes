<?php

// src/Controller/Store/Utils/StoreReviewTrait.php

namespace App\Controller\Store\Utils;

use App\Repository\Review\ReviewRepository;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Handle reviews for Store Detail page.
 */
trait StoreReviewTrait
{
    protected ReviewRepository $reviewRepo;
    protected ?array $reviewsAll;

    #[Required]
    public function setStoreReviewRepository(ReviewRepository $reviewRepo): void
    {
        $this->reviewRepo = $reviewRepo;
    }

    protected function reviews(int $product)
    {
        $reviews = $this->reviewRepo->productReviews(5, 4, $product);

        // The number of reviews to display per page
        $page_size = 3;

        // Calculate total number of reviews, and total number of pages
        $total_records = count($reviews);
        $total_pages = ceil($total_records / $page_size);

        // Validation: Page to display can not be greater than the total number of pages
        if ($page > $total_pages) {
            $page = $total_pages;
        }

        // Validation: Page to display can not be less than 1
        if ($page < 1) {
            $page = 1;
        }

        // Calculate the position of the first record of the page to display
        $offset = ($page - 1) * $page_size;

        // Extract the subset of reviews to be displayed from the array
        $result = array_slice($reviews, $offset, $page_size);

        $this->reviewsAll = $result;
    }
}
