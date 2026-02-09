<?php

// src/Controller/Admin/AdminProductDataController.php

namespace App\Controller\Admin;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor as Color;
use App\Entity\Product\ProductData\ProductData;
use App\Entity\Supplier\Supplier;
use App\Entity\Supplier\SupplierData;
use App\Form\Product\ProductData\ProductDataForm;
use App\Form\Search\SearchForm;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Product\Product\ProductRepository as ProductRepo;
use App\Repository\Product\ProductColor\ProductColorRepository as ColorRepo;
use App\Repository\Product\ProductData\ProductData2Repository as Data2Repo;
use App\Repository\Product\ProductData\ProductDataRepository as DataRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface as Slug;

/**
 * Display all Product Data, edit and delete individual Product Data.
 */
class AdminProductDataController extends AbstractController
{
    use Attributes;

    /**
     * Display all Product Data.
     */
    public function index(Request $r, ProductRepo $productRepo, Data2Repo $dataRepo, Paginator $pg, ORM $em): Response
    {
        $s = new Search();
        $searchForm = $this->createForm(SearchForm::class, $s);
        $searchForm->handleRequest($r);

        $q = $r->query;
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $pcUrl = $q->has('pc_url') ? $q->get('pc_url') : '';
        $sort = $q->has('sort') ? $q->get('sort') : 'p.updated_at';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $productName = $q->has('product_name') ? $q->get('product_name') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $colorName = $q->has('colors_full') ? $q->get('colors_full') : '';
        $fabrics = $q->has('fabrics') ? $q->get('fabrics') : '';
        $fabricName = $q->has('fabrics_full') ? $q->get('fabrics_full') : '';
        $size = $q->has('size') ? $q->get('size') : '';
        $supplier = $q->has('supplier') ? $q->get('supplier') : '';
        $supplierName = $q->has('supplier_name') ? $q->get('supplier_name') : '';

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $productDataSet = $dataRepo->search($s, $q);
            $searchTerm = $s->search();
        } elseif (
            (empty($s->search()) and empty($s->suppliers()))
            or !empty($pc) or !empty($brand) or !empty($product)
            or !empty($size) or !empty($supplier)
            or !empty($colors) or !empty($fabrics)) {
            $productDataSet = $dataRepo->all($q);
            $searchTerm = null;
        }

        /*  Collect any column filter with their respective label */
        $filter = [];
        $columns = ['brand' => $brand, 'supplier' => $supplierName,
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

        //  Retrieving items per page number
        $pages = [25, 50, 75, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($productDataSet) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $r->query->get('items_page');
            $maxPage = (int) ceil(count($productDataSet) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        // Paginate the results
        $productData = $pg->paginate($productDataSet, $page, $itemsPage);

        return $this->render('admin/2_product_data/index.html.twig', [
            'productData' => $productData,
            'items_page' => $itemsPage,
            'search' => $searchTerm,
            'maxPage' => $maxPage,
            'searchForm' => $searchForm,
            'pages' => $pages,
            'get' => $r->query->all(),
            'filters' => $filter,
        ]);
    }

    /**
     * Display all ProductData of Product grouped by their respective ProductColors.
     */
    public function colors(Request $r, Color $pc, DataRepo $dataRepo, ColorRepo $colorRepo, Paginator $pg): Response
    {
        $productDataSet = $dataRepo->pc($pc->getId());
        $thumbnails = [];
        if (empty($productDataSet)) {
            $thumbnails = $colorRepo->thumbnailSet($pc->getId());
        }

        // search & display products
        $s = new Search();
        $searchForm = $this->createForm(SearchForm::class, $s);
        $searchForm->handleRequest($r);
        $q = $r->query;
        $searchTerm = null;

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_data_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        //  Retrieving items per page number
        $pages = [25, 50, 75, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($productDataSet) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $r->query->get('items_page');
            $maxPage = (int) ceil(count($productDataSet) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        // Paginate the results
        $productData = $pg->paginate($productDataSet, $page, $itemsPage);

        return $this->render('admin/2_product_data/index.html.twig', [
            'searchForm' => $searchForm,
            'items_page' => $itemsPage,
            'search' => $searchTerm,
            'productData' => $productData,
            'thumbnails' => $thumbnails,
            'maxPage' => $maxPage,
            'pages' => $pages,
            'get' => $r->query->all(),
        ]);
    }

    /**
     * Create new Product Data.
     */
    public function new(Request $r, Product $product, ?Supplier $supplier, ProductRepo $productRepo, Data2Repo $dataRepo, Slug $slug, ORM $em): Response
    {
        /* Search & display products */
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $searchTerm = '';

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_data_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        $pd = new ProductData();
        $pd->setProduct($product);

        $supplierData = new SupplierData();
        $supplierData->addProduct($pd);

        if (!('socks' == $product->getType())) {
            if ('kids' == $product->getCategory()) {
                $allSizes = $this->getKidSizes();
            } else {
                $allSizes = $this->getAdultSizes();
            }
        } elseif ('socks' == $product->getType()) {
            $allSizes = $this->getSockSizes();
        }

        $productColors = [];
        $form = $this->createForm(ProductDataForm::class, $pd);
        $form->handleRequest($r);

        $pdBySupplier = [];
        $productColors = [];
        $sizeAll = [];
        $productImage = [];
        $duplicateColors = [];
        $qtyBySupplier = [];
        $sizes = [];
        $supplierColor = [];
        $suppliers = [];

        $pd = $productRepo->full($product->getId());
        foreach ($pd['colors'] as $key => $color) {
            $existingColors[$color['pcId']] = $color['pcId'];
        }

        /* Retrieve all productData */
        $pdSet = $dataRepo->fetchByProduct($product->getId());

        /* Retrieve supplier of each productData and group their available colors and sizes */
        if (!empty($pdSet)) {
            foreach ($pdSet as $i => $pData) {
                $pdBySupplier[$pData['supplierId']][]
                  = $pData;
                $qtyBySupplierSet[$pData['supplierId']][]
                  = $pData['qtyInStock'];

                $sizeAll[$pData['colorId']][''.$pData['size'].''] =
                      $pData['sellingPrice'];
            }

            foreach ($pdBySupplier as $i => $val) {
                foreach ($pdBySupplier[$i] as $j => $value) {
                    $suppliers[$i][$pdBySupplier[$i][$j]['colorId']][]
                            = $pdBySupplier[$i][$j]['size'];
                }
            }
        }

        // Group productData by supplier and their quantities
        if (!empty($qtyBySupplierSet)) {
            foreach ($qtyBySupplierSet as $i => $supplier) {
                $qtyBySupplier[$i] = array_sum($supplier);
            }
        }

        // sort sizes values
        if (!empty($sizeAll)) {
            foreach ($sizeAll as $color => $sizeArray) {
                asort($sizeArray);
                $sizes[$color] = $sizeArray;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $pd = $form->getData();
            /* The productData is persisted so that the ID can be
               assigned to it. This ID will be used in the SKU.  */
            $em->persist($pd);

            // Generate SKU (Stock-Keeping Unit)
            if ($pd->getColor()) {
                if ($pd->getColor()->getColor()) {
                    $color = $pd->getColor()->getColor();
                    $color = strtoupper(substr((string) $color, 0, 2));
                } else {
                    $color = 'BLANK';
                }

                $brand = $pd->getProduct()->getBrand();
                $category = $pd->getProduct()->getCategory();
                $type = $pd->getProduct()->getType();
                $sizes = $this->sizes();

                foreach ($sizes as $i => $sizesArray) {
                    if (in_array($pd->getSize(), $sizesArray, true)) {
                        if ('Socks sizes' != $i) {
                            $size = (int) $pd->getSize() * 10;
                        } else {
                            $size = $pd->getSize();
                        }
                    }
                }

                $id = $pd->getId();
                // last 3 digits
                $id = substr((string) $id, -3);
                // first two letters
                $brand = strtoupper(substr((string) $brand, 0, 2));
                // first letter
                $category = strtoupper(substr((string) $category, 0, 1));
                // first three letters
                $type = strtoupper(substr((string) $type, 0, 3));

                /* Ex. M[MEN] +
                       BAS[BASKETBALL] +
                       RE[REEBOK] +
                       137[137] +
                       75[7.5] +
                       BL[BLACK]
                      = MBASRE13775BL
                */
                $SKU = $category.$type.$brand.$id.$size.$color;
            }

            $pd = $pd->setSku($SKU);

            /* Set the selling price in case a counterpart productData exists
              (counterpart: a productData with the same size and color but
              with different supplier)
            */
            $color = $pd->getColor()->getColor();
            if (array_key_exists($color, $sizes)) {
                $size = (string) $pd->getSize();
                if (array_key_exists($size, $sizes[$color])) {
                    $sellingPrice = $sizes[$color][$size];
                    $pd = $pd->setSellingPrice($sellingPrice);
                }
            }

            $updated = $pd->getUpdated();
            $updatedColor = $pd->getColor()->getUpdated();

            // First flush
            $em->flush();

            /*
            Compare dates of (ProductSize, ProductColor) with that of
            ProductData to make that any update is reflected in the ProductData
            $updated date.
            Since the '$updated' attribute of a table is updated only when it detects changes across the whole table, the productData above is flushed first to activate the  @ORM\HasLifecycleCallbacks() below of Timestamps.
            */
            // /**
            //  * @ORM\PreUpdate
            //  */
            // public function setUpdated()
            // {
            //    $this->updated = new \DateTime("now");
            //    return $this;
            // }
            if ($updatedColor > $updated) {
                $pd = $pd->setUpdated();
            }

            // Second flush
            $em->persist($pd);
            $em->flush();

            $supplier = $pd->getSupplier()->getSupplier()->getId();
            $product = $product->getId();

            $new = 'admin_product_data_new';
            $index = 'admin_product_data_index';
            $options =
            [
                'product' => $product,
                'supplier' => $supplier];
            $a = Response::HTTP_SEE_OTHER;

            if ($form->get('new')->isClicked()) {
                return $this->redirectToRoute($new, $options, $a);
            }

            return $this->redirectToRoute($index, [], $a);
        }

        $template = 'admin/2_product_data/edit.html.twig';
        $options =
        [
            'form' => $form,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'product' => $pd,
            'suppliers' => $suppliers,
            'qtyBySupplier' => $qtyBySupplier,
            'sizes' => $sizes,
            'allSizes' => $allSizes,
            'allColors' => $existingColors];

        return $this->render($template, $options);
    }

    /**
     * Edit individual Product Data.
     */
    public function edit(Request $r, ProductData $productData, ProductRepo $productRepo, DataRepo $dataRepo, Data2Repo $data2Repo, ItemRepo $itemRepo, ORM $em, Slug $slug, ?Supplier $supplier = null): Response
    {
        $pd = $dataRepo->full($productData->getId());
        $product = $pd['productId'];

        foreach ($pd['colors'] as $key => $color) {
            $existingColors[$color['pcId']] = $color['pcId'];
        }

        $filesystem = new Filesystem();
        $current_dir_path = getcwd();

        // retrieve product_data
        $productDataSet = $data2Repo->fetchByProduct($product);
        $pdBySupplier = [];
        $productColors = [];
        $sizeAll = [];
        $qtyBySupplier = [];
        $sizes = [];

        // Retrieve supplier of each productData and group their available colors and sizes
        if (!empty($productDataSet)) {
            foreach ($productDataSet as $i => $pData) {
                $pdBySupplier[$pData['supplierId']][]
                  = $pData;
                $qtyBySupplierSet[$pData['supplierId']][]
                  = $pData['qtyInStock'];

                $sizeAll[$pData['colorId']][''.$pData['size'].''] =
                      $pData['sellingPrice'];
            }

            foreach ($pdBySupplier as $i => $val) {
                foreach ($pdBySupplier[$i] as $j => $value) {
                    $suppliers[$i][$pdBySupplier[$i][$j]['colorId']][]
                            = $pdBySupplier[$i][$j]['size'];
                }
            }
        }

        // Group productData by supplier and their quantities
        if (!empty($qtyBySupplierSet)) {
            foreach ($qtyBySupplierSet as $i => $supplier) {
                $qtyBySupplier[$i] = array_sum($supplier);
            }
        }

        // sort sizes values
        if (!empty($sizeAll)) {
            foreach ($sizeAll as $color => $sizeArray) {
                asort($sizeArray);
                $sizes[$color] = $sizeArray;
            }
        }

        $form = $this->createForm(ProductDataForm::class, $productData);
        $form->handleRequest($r);

        // search & display products
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_data_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        // Form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $productData = $form->getData();
            $supplier = $productData->getSupplier()->getSupplier();

            // Generate SKU (Stock-Keeping Unit)
            if ($productData->getColor()) {
                if ($productData->getColor()->getColor()) {
                    $color = $productData->getColor()->getColor();
                    $color = strtoupper(substr((string) $color, 0, 2));
                } else {
                    $color = 'BLANK';
                }

                $id = $productData->getId();
                $brand = $productData->getProduct()->getBrand();
                $category = $productData->getProduct()->getCategory();
                $type = $productData->getProduct()->getType();
                if ('integer' == gettype($productData->getSize())) {
                    $size = $productData->getSize() * 10;
                } elseif ('string' == gettype($productData->getSize())) {
                    $size = $productData->getSize();
                }

                $id = substr((string) $id, -3);                  // last 3 digits
                $brand = strtoupper(substr((string) $brand, 0, 2));    // first two letters
                $category = strtoupper(substr((string) $category, 0, 1)); // first letter
                $type = strtoupper(substr((string) $type, 0, 3));  // first three letters

                // ex. M[MEN] + BAS[BASKETBALL]+ RE[REEBOK] + 137[137] + 75[7.5] + BL[BLACK]
                //     = MBASRE13775BL
                $SKU = $category.$type.$brand.$id.$size.$color;
            }

            $productData = $productData->setSku($SKU);

            // Set the selling price in case a counterpart productData exists
            $color = $productData->getColor()->getColor();
            if (array_key_exists($color, $sizes)) {
                $size = (string) $productData->getSize();
                if (array_key_exists($size, $sizes[$color])) {
                    $sellingPrice = $sizes[$color][$size];
                    if (0 != $productData->getSellingPrice()
                        && $productData->getSellingPrice() == $sellingPrice) {
                        $productData = $productData->setSellingPrice($sellingPrice);
                    }
                }
            }

            $updatedPrice = $productData->getSellingPrice();

            // Mirror the current price to all counterpart productData (same size and same color)
            $mirrorProductData = $data2Repo->findByMirror([
                'product' => $product,
                'size' => $size,
                'color' => $color,
            ]);

            if (!empty($mirrorProductData)) {
                foreach ($mirrorProductData as $i => $pData) {
                    $mirrorProductData[$i] = $pData->setSellingPrice($updatedPrice);
                    $em->persist($pData);
                    $em->flush();
                }
            }

            $updated = $productData->getUpdated();
            $updatedColor = $productData->getColor()->getUpdated();

            // First flush
            $em->persist($productData);
            $em->flush();

            /*
            Compare dates of (ProductSize, ProductColor) with that of
            ProductData to ensure that all updates are reflected in the ProductData
            $updated attribute.
            Since the $updated is updated only when it detects changes inside the whole table, the productData above is flushed first to activate the  @ORM\HasLifecycleCallbacks() of updated Timestamps.
            */
            if ($updatedColor > $updated) {
                $productData = $productData->setUpdated();
            }

            // Second flush
            $em->persist($productData);
            $em->flush();

            $supplier = $productData->getSupplier()->getSupplier()->getId();
            // $product = $product->getId();

            // If 'New' button is clicked, redirect to new Product Data form.
            if ($form->get('new')->isClicked()) {
                return $this->redirectToRoute(
                    'admin_product_data_new',
                    [
                        'product' => $product,
                        'supplier' => $supplier,
                    ],
                    Response::HTTP_SEE_OTHER
                );
            }

            return $this->redirectToRoute('admin_product_data_index', [], Response::HTTP_SEE_OTHER);
        }

        $category = $pd['category'];
        $type = $pd['type'];

        if ('socks' == $type) {
            $sizeSet = $this->getSockSizes();
        } elseif ('Kids' == $category) {
            $sizeSet = $this->getKidSizes();
        } else {
            $sizeSet = $this->getAdultSizes();
        }

        /* Check if the ProductData can be deleted.
           It can only be deleted if it hasn't been
           added in a cart yet.
         */
        $delete = $itemRepo->checkProduct($pd['id']);

        return $this->render(
            'admin/2_product_data/edit.html.twig',
            [
                'product' => $pd,
                'sizes' => $sizes,
                'allSizes' => $sizeSet,
                'suppliers' => $suppliers,
                'qtyBySupplier' => $qtyBySupplier,
                'form' => $form,
                'searchForm' => $searchForm,
                'search' => '',
                'colors' => $productColors,
                'allColors' => $existingColors,
                'brands' => $this->brands(),
                'categories' => $this->getCategory(),
                'delete' => $delete,
            ]
        );
    }

    /**
     * Delete Product Data and all its children.
     */
    public function delete(Request $r, ORM $em, ProductData $productData): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productData->getId(), $r->request->get('_token'))) {
            $em->remove($productData);
            $em->flush();
        }

        return $this->redirectToRoute('admin_product_data_index', [], Response::HTTP_SEE_OTHER);
    }
}
