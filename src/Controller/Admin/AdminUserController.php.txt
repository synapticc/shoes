<?php

// src/Controller/Admin/AdminUserController.php

namespace App\Controller\Admin;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Entity\User\User;
use App\Entity\User\UserDeactivate;
use App\Form\Search\SearchForm;
use App\Repository\User\Session\PageViewRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminUserController extends AbstractController
{
    use Attributes;

    public function index(Request $r, UserRepository $userRepo, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        $q = $r->query;
        $sort = $q->has('sort') ? $q->get('sort') : 'updated';
        $order = $q->has('order') ? $q->get('order') : 'DESC';

        if (empty($search->search())) {
            $activeUsers = $userRepo->activeUsers();
            $searchTerm = null;
        } elseif (!(empty($search->search()))) {
            $searchTerm = $search->search();
            $activeUsers = $userRepo->searchActive($search, $q);
        }

        $pages = $this->itemsRange();
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($activeUsers) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $r->query->get('items_page');
            $maxPage = (int) ceil(count($activeUsers) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        /*  Create the following associative arrays:
          1) 'calling code' => ['country name', 'country code']
          2) 'country code' => ['country name', ''country flag]
        */
        $countries = countries();
        foreach ($countries as $key => $country) {
            $fullPhoneCodes[$country['calling_code']] =
            [
                'country' => $country['name'],
                'code' => $country['iso_3166_1_alpha2'],
            ];

            $fulllCountryNames[$country['iso_3166_1_alpha2']] =
            [
                'country' => $country['name'],
                'flag' => $country['emoji'],
            ];
        }

        // Paginate the results
        $users = $pg->paginate($activeUsers, $page, $itemsPage);

        return $this->render('admin/5_user/index.html.twig', [
            'users' => $users,
            'items_page' => $itemsPage,
            'search' => $searchTerm,
            'maxPage' => $maxPage,
            'searchForm' => $searchForm,
            'pages' => $pages,
            'countryCodes' => $fullPhoneCodes,
            'countryNames' => $fulllCountryNames,
            'get' => $r->query->all(),
        ]);
    }

    public function deleted(Request $r, UserRepository $userRepo, Paginator $pg): Response
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('+4'));
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        if (empty($search->search())) {
            $deletedUsers = $userRepo->findDeletedUsers();
            $searchTerm = null;
        } elseif (!(empty($search->search()))) {
            $searchTerm = $search->search();
            $deletedUsers = $userRepo->searchDeleted($search, $q);
        }

        $pages = $this->itemsRange();
        $itemsPage = $pages[0];

        // Paginate the results.
        $page = $r->query->getInt('page', 1);
        $deletedUsers = $pg->paginate($deletedUsers, $page, $itemsPage);

        /*  Create the following associative arrays:
          1) 'calling code' => ['country name', 'country code']
          2) 'country code' => ['country name', ''country flag]
        */
        $countries = countries();
        foreach ($countries as $key => $country) {
            $fullPhoneCodes[$country['calling_code']] =
            [
                'country' => $country['name'],
                'code' => $country['iso_3166_1_alpha2'],
            ];

            $fulllCountryNames[$country['iso_3166_1_alpha2']] =
            [
                'country' => $country['name'],
                'flag' => $country['emoji'],
            ];
        }

        return $this->render('admin/5_user/index.html.twig', [
            'users' => $deletedUsers,
            'items_page' => $itemsPage,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'pages' => $pages,
            'countryCodes' => $fullPhoneCodes,
            'countryNames' => $fulllCountryNames,
            'get' => $r->query->all(),
        ]);
    }

    public function show(User $user, PageViewRepository $pageViewRepo, Request $r, Paginator $pg): Response
    {
        $pageViews = $pageViewRepo->user($user);

        $itemsPage = 10;
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $r->query->get('items_page');
            $maxPage = (int) ceil(count($pageViews) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        // Paginate the results
        $pageViews = $pg->paginate($pageViews, $page, $itemsPage);

        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_users_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'admin/5_user/show.html.twig',
            [
                'user' => $user,
                'searchForm' => $searchForm,
                'pageViews' => $pageViews,
            ]
        );
    }

    public function edit(Request $r, User $user, EntityManagerInterface $em): JsonResponse
    {
        $submittedToken = $r->getPayload()->get('_token');
        if ($this->isCsrfTokenValid('edit-user', $submittedToken)) {
            // Retrieve 'roles' from  query parameters
            $roles = [];
            if ($r->request->has('roles')) {
                $roles = $r->request->all()['roles'];
            }

            // If 'roles' is absent in query parameters, assign 'ROLE_USER' to $roles
            if (empty($roles)) {
                $roles = ['ROLE_USER'];
            }

            // Retrieve 'deactivate' from  query parameters
            $deactivate = false;
            if ($r->request->has('deactivate')) {
                $deactivate = (bool) $r->request->get('deactivate');
            }

            if ($deactivate) {
                /* Prepend 'deleted_ and append a unique ID to
                  user identifier.*/
                $identifier = $user->getUserIdentifier();

                // Generate unique ID
                $uniqueId = bin2hex(random_bytes(6));
                $updatedIdentifier = 'deleted_'.$identifier.'_'.$uniqueId;

                $user->setEmail($updatedIdentifier);
            } else {
                $identifier = $user->getUserIdentifier();

                if (str_contains($identifier, 'deleted_')) {
                    $parts = explode('_', $identifier);
                    $originalEmail = $parts[1];
                    $user->setEmail($originalEmail);
                }
            }

            if (!empty($user->getUserDeactivate())) {
                $userDeactivate = $user->getUserDeactivate();
            } else {
                $userDeactivate = new UserDeactivate();
            }

            // Update the user and flush
            $userDeactivate->setDeactivate($deactivate);
            $user->setUserDeactivate($userDeactivate)
                 ->setRoles($roles)
                 ->setUpdated();

            $em->persist($user);
            $em->flush();
        }

        return $this->json(
            $this->renderView(
                'admin/5_user/partials/_edit-update.html.twig',
                ['user' => $user]
            )
        );
    }

    public function delete(Request $r, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $r->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
