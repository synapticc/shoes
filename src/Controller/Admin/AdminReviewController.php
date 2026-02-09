<?php

// src/Controller/Admin/AdminReviewController.php

namespace App\Controller\Admin;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Entity\Review\Review;
use App\Form\Review\AdminReviewForm;
use App\Form\Search\SearchForm;
use App\Repository\Review\ReviewRepository as ReviewRepo;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminReviewController extends AbstractController
{
    use Attributes;

    /* path: /admin/reviews */
    public function index(Request $r, ReviewRepo $reviewRepo, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        if (empty($search->search())) {
            $reviewSet = $reviewRepo->adminReviews($q);
            $searchTerm = null;
        } elseif (!(empty($search->search()))) {
            $reviewSet = $reviewRepo->search($search, $q);
            $searchTerm = $search->search();
        }

        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $productName = $q->has('product_name') ? $q->get('product_name') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $pcUrl = $q->has('pc_url') ? $q->get('pc_url') : '';
        $colorName = $q->has('colors_full') ? $q->get('colors_full') : '';
        $fabrics = $q->has('fabrics') ? $q->get('fabrics') : '';
        $fabricName = $q->has('fabrics_full') ? $q->get('fabrics_full') : '';
        $size = $q->has('size') ? $q->get('size') : '';
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';

        /*  Collect any column filter with their respective label */
        $filter = [];
        $columns = ['brand' => $brand, 'customer_email' => $email,
            'pc_url' => $pcUrl, 'product' => $productName,
            'colors' => $colorName, 'fabrics' => $fabricName,
            'size' => $size,
        ];

        foreach ($columns as $key => $column) {
            if (!empty($column)) {
                $filter['column'] = $key;
                $filter['value'] = $column ? $column : [];
            }
        }

        /* Paginate the results | Start */
        $pages = [15, 25, 50, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($reviewSet) / $itemsPage);
        $page = $q->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($reviewSet) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }
        $reviews = $pg->paginate($reviewSet, $page, $itemsPage);
        /* Paginate the results | End */

        return $this->render('/admin/3_review/index.html.twig', [
            'reviews' => $reviews,
            'searchForm' => $searchForm,
            'get' => $r->query->all(),
            'pages' => $pages,
            'search' => $searchTerm,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'filters' => $filter,
            'sliderFit' => $this->sliderFit(),
            'sliderWidth' => $this->sliderWidth(),
            'sliderComfort' => $this->sliderComfort(),
        ]);
    }

    /* path: /admin/reviews/{id}/edit */
    public function edit(Request $r, Review $review, ReviewRepo $reviewRepo): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Review Index to search for Review.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_review_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        $q = $r->query;

        $searchTerm = '';
        if (!(empty($search->search()))) {
            $reviewSet = $reviewRepo->search($search, $q);
            $searchTerm = $search->search();
        }

        $form = $this->createForm(AdminReviewForm::class, $review);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $reviewRepo->save($review->setUpdated());

            return $this->redirectToRoute('admin_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/3_review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
        ]);
    }

    /**
     * Delete the Review and the respective review image as well.
     *
     * Path: /admin/reviews/{id}
     */
    public function delete(Request $request, Review $review, ReviewRepo $reviewRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            if (!empty($review->getReviewImage())) {
                $imagePath = $review->getReviewImage()->getImage();

                if (!empty($imagePath)) {
                    $filesystem = new Filesystem();
                    $current_dir_path = getcwd();
                    $imageToRemove = [$current_dir_path.'/'.$imagePath];
                    try {
                        // remove binary images files
                        $filesystem->remove($imageToRemove);
                    } catch (IOExceptionInterface $exception) {
                        echo 'Error deleting directory at'.$exception->getPath();
                    }
                }
            }

            $reviewRepo->remove($review);
        }

        return $this->redirectToRoute('admin_review_index', [], Response::HTTP_SEE_OTHER);
    }
}
