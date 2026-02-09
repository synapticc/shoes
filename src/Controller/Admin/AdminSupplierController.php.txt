<?php

// src/Controller/Admin/AdminSupplierController.php

namespace App\Controller\Admin;

use App\Controller\_Utils\Attributes;
use App\Controller\Admin\Paginator\Paginator;
use App\Entity\NoMap\Search\Search;
use App\Entity\Supplier\Supplier;
use App\Form\Search\SearchForm;
use App\Form\Supplier\SupplierForm;
use App\Repository\Product\ProductData\ProductData2Repository as Data2Repo;
use App\Repository\Supplier\SupplierRepository as SupplierRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSupplierController extends AbstractController
{
    use Attributes;

    public function index(
        Request $request,
        SupplierRepo $supplierRepo,
        Paginator $paginator,
        Search $search,
    ): Response {
        // Create search form
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);

        // Retrieve suppliers by search name
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = $search->search();
            $supplierSet = $supplierRepo->search($search);
        } else {
            $supplierSet = $supplierRepo->findBy([], ['updated' => 'DESC']);
            $searchTerm = null;
        }

        // Paginate results.
        $page = $paginator->paginate($supplierSet);

        $countries = countries();

        // Extract flag of each country.
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

        return $this->render('admin/4_supplier/index.html.twig', [
            'suppliers' => $page['items'],
            'pages' => $page['pages'],
            'items_page' => $page['items_page'],
            'maxPage' => $page['maxPage'],
            'countryCodes' => $fullPhoneCodes,
            'countryNames' => $fulllCountryNames,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'get' => $request->query->all(),
        ]);
    }

    public function new(Request $request, SupplierRepo $supplierRepo, ORM $em, Search $search): Response
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierForm::class, $supplier);
        $form->handleRequest($request);

        // Create search form
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);

        // Retrieve suppliers by search name
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_supplier_index', $request->query->all(), Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $supplier = $form->getData();
            $country_code = $supplier->getCountryCode();
            /* Retrieve the full English name of the country
              from its country code. */
            $country = locale_get_display_region("sl-Latn-$country_code-nedis", 'en');
            $supplier->setCountry($country);
            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute('admin_supplier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/4_supplier/new.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
            'search' => '',
            'searchForm' => $searchForm,
        ]);
    }

    public function edit(Request $request, Supplier $supplier, ORM $em, Data2Repo $dataRepo, Search $search): Response
    {
        $form = $this->createForm(SupplierForm::class, $supplier);
        $form->handleRequest($request);

        // Create search form
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);

        // Search for suppliers.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_supplier_index', $request->query->all(), Response::HTTP_SEE_OTHER);
        }

        $checkDelete = $dataRepo->checkSupplier($supplier->getId());

        // Retrieve suppliers by search name
        $searchTerm = '';
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if (!empty($search->search())) {
                $searchTerm = $search->search();
                $options = ['search_form' => ['q' => $searchTerm]];

                return $this->redirectToRoute('admin_supplier_index', $options, Response::HTTP_SEE_OTHER);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $supplier = $form->getData();
            $country_code = $supplier->getCountryCode();
            /* Retrieve the full English name of the country
              from its country code. */
            $country = locale_get_display_region("sl-Latn-$country_code-nedis", 'en');
            $supplier->setCountry($country);
            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute(
                'admin_supplier_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('admin/4_supplier/edit.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
            'search' => $searchTerm,
            'searchForm' => $searchForm,
            'checkDelete' => $checkDelete,
        ]);
    }

    public function delete(Request $request, Supplier $supplier, SupplierRepo $supplierRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$supplier->getId(), $request->request->get('_token'))) {
            $supplierRepo->remove($supplier);
        }

        return $this->redirectToRoute('admin_supplier_index', [], Response::HTTP_SEE_OTHER);
    }
}
