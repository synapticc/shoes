<?php

// src/Controller/Admin/Product/AdminProductController.php

namespace App\Controller\Admin\Product;

use App\Controller\_Utils\Attributes;
use App\Controller\Admin\Paginator\Paginator;
use App\Entity\Billing\Order;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ExcludeColor;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductColor\ProductColorTags as Tags;
use App\Entity\Product\ProductColor\ProductColorTexture as Texture;
use App\Entity\Product\ProductColor\ProductColorVideo as Video;
use App\Entity\Product\ProductColor\SimilarProductColor as Similar;
use App\Form\Product\Product\ProductDisplayForm as DisplayForm;
use App\Form\Product\Product\ProductForm;
use App\Form\Search\SearchForm;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Billing\OrderRepository as OrderRepo;
use App\Repository\Product\Product\ProductRepository as ProductRepo;
use App\Repository\Product\Product\SimilarProductRepository as SimilarRepo;
use App\Repository\Product\ProductColor\ProductColorRepository as ColorRepo;
use App\Repository\Product\ProductData\ProductData2Repository as Data2Repo;
use App\Repository\Product\ProductData\ProductDataRepository as DataRepo;
use App\Repository\Review\ReviewRepository as ReviewRepo;
use App\Repository\User\Session\PageViewRepository as PageRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as Url;
use Symfony\Component\String\Slugger\SluggerInterface as Slug;

/**
 * Display all Products, edit and delete Product.
 */
class AdminProductController extends AbstractController
{
    use Attributes;
    use HandleProduct;

    public function __construct(private Slug $slug, private ProductRepo $productRepo, private ColorRepo $colorRepo, private ORM $em)
    {
    }

    /**
     * Display all Products.
     */
    public function index(Request $r, ProductRepo $productRepo, DataRepo $data, Search $search, Paginator $paginator): Response
    {
        // Create Display form.
        $displayForm = $this->createForm(DisplayForm::class);
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        // Retrieve various query parameters.
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $categoryName = $q->has('category_full') ? $q->get('category_full') : '';
        $occasionName = $q->has('occasion_full') ? $q->get('occasion_full') : '';
        $typeName = $q->has('type_full') ? $q->get('type_full') : '';

        // Search specific Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = $search->search();
            $productSet = $productRepo->search($search, $q);
        } else {
            $productSet = $productRepo->all($q);
            $searchTerm = null;
        }

        /*  Retrieve any column filter with their respective label, from the URL. */
        $filter = [];
        $columns = ['brand' => $brand, 'category' => $categoryName,
            'occasion' => $occasionName, 'type' => $typeName];

        foreach ($columns as $key => $column) {
            if (!empty($column)) {
                $filter['column'] = $key;
                $filter['value'] = $column ? $column : [];
            }
        }

        // Paginate results.
        $page = $paginator->paginate($productSet);

        $template = 'admin/1_product/index.html.twig';
        $options =
        [
            'products' => $page['items'],
            'pages' => $page['pages'],
            'items_page' => $page['items_page'],
            'maxPage' => $page['maxPage'],
            'searchForm' => $searchForm,
            'formObject' => $displayForm,
            'search' => $searchTerm,
            'get' => $q->all(),
            'filters' => $filter,
        ];

        return $this->render($template, $options);
    }

    /**
     * Create new Product.
     */
    public function new(Request $r, ProductRepo $productRepo, ColorRepo $colorRepo, Search $search, ORM $em, Slug $slug): Response
    {
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        // Create Product & Product form.
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($r);

        // Process form.
        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve form data.
            $product = $form->getData();
            // dd($product);
            $name = $product->getName();
            $brand = $product->getBrand();
            $category = $product->getCategory();
            $requestPro = $r->get('product_form');
            $c = 'colors';
            $colorForm = isset($requestPro[$c]) ? $requestPro[$c] : '';

            // Check if product with same name and brand already exists.
            $existingProduct =
              $productRepo->findBy(['name' => $name, 'brand' => $brand,
                  'category' => $category]);

            /* Display flash error message if a product with that same name, brand
              and category already exists. */
            if (!empty($existingProduct)) {
                // example:  Fresh foam already exists in New Balance (Men).
                $message = $name.' already exists in '.$this->name($brand).' ('.
                           $this->name($category).'). ';

                $this->addFlash('product_exists', $message);

                // Redirect to Product Edit form.
                return $this->redirectToRoute('admin_product_edit', $product->getId());
            }

            if (!empty($colorForm)) {
                foreach ($colorForm as $key => $p) {
                    // Create new ProductColor.
                    $color[$key] = new ProductColor();
                    // Assign Product to new ProductColor.
                    $color[$key] = $color[$key]->setProduct($product);

                    /*
                      // NOTE: There are multiple ways to store the color
                      combination.
                        - JSON
                        - Text Array
                        - String

                    I've chosen the string format since the color is usually
                    spelled as one color word, say, 'Black White'.

                    Combining the selected colors into one string (a color combination).
                      Ex. black-white
                          dark_brown-black-grey
                    */
                    $c1 = 'color1';
                    $c2 = 'color2';
                    $c3 = 'color3';

                    if (!empty($p[$c1]) && empty($p[$c2]) && empty($p[$c3])) {
                        $pcColor = $p[$c1];
                    } elseif (!empty($p[$c1]) && !empty($p[$c2]) && empty($p[$c3])) {
                        $pcColor = $p[$c1].'-'.$p[$c2];
                    } elseif (!empty($p[$c1]) && !empty($p[$c2]) && !empty($p[$c3])) {
                        $pcColor = $p[$c1].'-'.$p[$c2].'-'.$p[$c3];
                    }

                    // Set color, fabrics & textures.
                    $color[$key]->setColor($pcColor)
                                ->setFabrics($p['fabrics']);

                    // Assign textures.
                    if (!empty($p['textures']['textures'])) {
                        $texture = new Texture();
                        $texture->setTextures($p['textures']['textures']);
                        $color[$key]->setTextures($texture);
                    }

                    // Assign tags.
                    if (!empty($p['tags']['tags'])) {
                        $tags = new Tags();
                        $tags->setTags($p['tags']['tags']);
                        $color[$key]->setTags($tags);
                    }

                    // Create a new ProductColorVideo and assign video URL.
                    if (!empty($p['video']['videoUrl'])) {
                        $colorVideo = new Video();
                        $colorVideo->setVideoUrl($p['video']['videoUrl']);
                        $color[$key]->setVideo($colorVideo);
                    }

                    // The abbreviations below have been opted for naming simplicity.
                    $spc = 'similarProductColor';
                    $ec = 'excludeColor';
                    $epdc = 'excludeProductColors';

                    if (!empty($p[$spc])) {
                        // Create new SimilarProductColor.
                        $similarPC = new Similar();

                        if (!empty($p[$spc][$ec])) {
                            // Create new ExcludeColor.
                            $excludeColor = new ExcludeColor();
                            // Set exclude colors.
                            $excludeColor->setColors($p[$spc][$ec]['colors']);

                            // Add new ExcludeColor to new SimilarProductColor.
                            $similarPC->setExcludeColor($excludeColor);
                        }

                        if (!empty($p[$spc][$epdc])) {
                            // Create new ExcludeProductColor.
                            foreach ($p[$spc][$epdc] as $i => $excludePC) {
                                $excludeProductColor = new ExcludePC();
                                // Retrieve & assign ProductColor to new ExcludeProductColor.
                                $excludeProductColor->setColor($this->colorRepo->find($excludePC['color']));

                                // Add new ExcludeProductColor to new SimilarProductColor.
                                $similarPC->addExcludeProductColor($excludeProductColor);
                            }
                        }
                        // Add new SimilarProductColor to new ProductColor.
                        $color[$key]->setSimilarProductColor($similarPC);
                    }

                    /*  Upload images URL to their corresponding ProductImage table. */
                    $i = 'image1';
                    $i2 = 'image2';
                    $i3 = 'image3';
                    $i4 = 'image4';
                    $i5 = 'image5';

                    if (!empty($form->get($c)[$key]->get($i)->getData())) {
                        $image[$key] = $form->get($c)[$key]->get($i)->getData();
                        $this->uploadToImage($color[$key], $image[$key], null);
                    }

                    if (!empty($form->get($c)[$key]->get($i2)->getData())) {
                        $image2[$key] = $form->get($c)[$key]->get($i2)->getData();
                        $this->uploadToImage($color[$key], $image2[$key], 2);
                    }

                    if (!empty($form->get($c)[$key]->get($i3)->getData())) {
                        $image3[$key] = $form->get($c)[$key]->get($i3)->getData();
                        $this->uploadToImage($color[$key], $image3[$key], 3);
                    }

                    if (!empty($form->get($c)[$key]->get($i4)->getData())) {
                        $image4[$key] = $form->get($c)[$key]->get($i4)->getData();
                        $this->uploadToImage($color[$key], $image4[$key], 4);
                    }

                    if (!empty($form->get($c)[$key]->get($i5)->getData())) {
                        $image5[$key] = $form->get($c)[$key]->get($i5)->getData();
                        $this->uploadToImage($color[$key], $image5[$key], 5);
                    }

                    // Add new ProductColor to new Product.
                    $product->addProductColor($color[$key]);
                }
            }

            $sm = 'similarProduct';
            if (isset($r->get('product')[$sm])) {
                $similarForm = $r->get('product')[$sm];
                // Create new SimilarProduct, set values and add to Product.
                $product = $this->handleSimilarProduct($product, $similarForm);
            }

            // Save if product with same name and brand doesn't exist.
            if (empty($productResult)) {
                $em->persist($product);
                $em->flush();

                return $this->redirectToRoute(
                    'admin_product_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('admin/1_product/edit.html.twig', [
            'form' => $form,
            'search' => '',
            'sortValues' => [],
            'searchForm' => $searchForm,
            'colors' => $this->getColors(),
            'fabrics' => $this->getfabrics(),
            'textures' => $this->getTexture(),
            'tags' => $this->getTag(),
        ]);
    }

    /**
     * Display individual Product and its detailed report.
     */
    public function show(int $id, Product $product, ProductRepo $productRepo, Data2Repo $data, ColorRepo $colorRepo, ItemRepo $item, PageRepo $page, OrderRepo $orderRepo, Request $r, Search $search, Paginator $pg): Response
    {
        // Retrieve ProductData with full details.
        $productDataSet = $data->details($id);
        $productData = $productDataSet['set'];
        // Retrieve size info of ProductData.
        $sizeSet = $productDataSet['size'];
        // Fetch the latest purchased OrderItem.
        $purchases = $item->latest($product, Order::STATUS_PAID);
        // Fetch the latest OrderItem added to cart.
        $cart = $item->latest($product, Order::STATUS_CART);
        // Retrieve the sum of purchased OrderItem grouped by color.
        $colorCart = $item->colorSet($id)['cart'];
        // Retrieve the sum of cart OrderItem grouped by color.
        $colorPaid = $item->colorSet($id)['paid'];
        // Retrieve Product with full details.
        $product = $productRepo->fetch($id)['product'];
        // Retrieve all ProductColor with their respective quantity.
        $colorSet = $colorRepo->quantity($id);
        // Retrieve the latest 15 PageViews.
        $pageViews = $page->latest($id);
        // Fetch Order total.
        $total = $orderRepo->productTotal($id);

        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'admin/1_product/show.html.twig',
            [
                'product' => $product,
                'productData' => $productData,
                'colorSet' => $colorSet,
                'colorCart' => $colorCart,
                'colorPaid' => $colorPaid,
                'sizeSet' => $sizeSet,
                'purchases' => $purchases,
                'cart' => $cart,
                'total' => $total,
                'pageViews' => $pageViews,
                'searchForm' => $searchForm,
                'search' => '',
                'get' => $r->query->all(),
            ]
        );
    }

    /**
     * Edit Product and its ProductColors.
     */
    public function edit(Request $r, Product $product, ProductRepo $productRepo, Data2Repo $data, ColorRepo $colorRepo, ReviewRepo $reviewRepo, Search $search, ORM $em, Slug $slug): Response
    {
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_product_index', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        // Create Product form.
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($r);

        $id = $product->getId();
        // Retrieve Product with full details.
        $productPack = $productRepo->fetch($id);
        $product = $productPack['product'];
        $sort = $productPack['sort'];
        $colorSelectSet = [];
        $similarPC = [];
        $colorIdSet = [];

        if (!empty($product)) {
            $colors = $product['colors'];
            $colorIdSet = array_keys($colors);
            $refColors = $this->getColors();

            if (count($colors) >= 1) {
                foreach (array_values($colors) as $i => $color) {
                    foreach (explode('-', (string) $color['color']) as $k => $splitColor) {
                        foreach ($refColors as $j => $refColor) {
                            if ($refColor['name'] == $splitColor) {
                                $fullNameColor[$i][$k] = $refColor['fullName'];
                            }
                        }
                    }

                    $colorSelectSet[$i]['name'] = $color['color'];
                    $colorSelectSet[$i]['fullName'] = implode(' / ', $fullNameColor[$i]);
                }
            }
        }

        if (!empty($product['soColor'])) {
            $similarProducts = $product[$sm];
            foreach ($similarProducts as $m => $similarProduct) {
                foreach ($similarProduct['soColors'] as $n => $pc) {
                    $similarPC[$pc['sopcId']] = $pc;
                }
            }
        }

        // Check if the ProductColors can be deleted.
        $checkColor = $data->checkColor($id);
        // Check if the Product can be deleted.
        $checkProduct = $data->checkProduct($id);
        // Check if the Product has been reviewed.
        $reviews = $reviewRepo->checkProduct($id);

        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve form data.
            $product = $form->getData();
            $sm = 'similarProduct';
            $pc = 'colors';

            // Retrieve SimilarProduct form data.
            $similarForm = isset($r->get('product')[$sm]) ? $r->get('product')[$sm] : '';

            // Handle SimilarProduct.
            $product = $this->handleSimilarProduct($product, $similarForm);
            $requestColor = $r->get('product_form')[$pc];
            $colorForm = isset($requestColor) ? $requestColor : '';

            if (empty($colorForm)) {
                // Delete images by whole set.
                foreach ($product->getProductColor() as $i => $productColor) {
                    $this->deleteFullImagesSet($productColor);
                }
            }

            if (!empty($colorForm)) {
                if (!$product->getProductColor()->isEmpty()) {
                    // Get ProductColor.
                    $colorSet = $product->getProductColor();

                    // Group all ProductColor IDs.
                    foreach ($colorSet as $i => $dataColor) {
                        $colorIdArray[] = $colorSet[$i]->getId();
                    }

                    // Group all ProductColor IDs from the form.
                    foreach ($colorForm as $i => $p) {
                        $colorFormIdArray[] = $p['colorId'];
                    }

                    /* Compare Form Data with Database and remove ProductColor ID from SimilarProduct 'sort'. */
                    if (count($colorFormIdArray) < count($colorIdArray)) {
                        // Determine the difference between the two arrays.
                        $colorRemove = array_diff($colorIdArray, $colorFormIdArray);

                        foreach ($colorRemove as $key => $id) {
                            // Fetch all Similar containing this ProductColor ID.
                            $similarSort = $productRepo->similarSort($id);

                            /* Delete the ProductColor ID from the Product's own Similar 'sort'. */
                            if (!empty($similarSort)) {
                                foreach ($similarSort as $i => $similar) {
                                    // Get Similar Product.
                                    $similarProduct = $similar->getSimilarProduct();
                                    $sort = $similarProduct->getSort();

                                    // Locate and delete ProductColor ID in 'sort', if any.
                                    if (in_array($id, $sort)) {
                                        $findKey = array_search($id, $sort);
                                        if (false !== $findKey) {
                                            unset($sort[$findKey]);
                                        }
                                    }

                                    $sort = array_values($sort);
                                    // Updated 'sort'.
                                    $similarProduct->setSort($sort);
                                    $similar->setSimilarProduct($similarProduct);
                                    // Save Similar.
                                    $em->persist($similar);
                                    $em->flush();
                                }
                            }

                            /* Delete the ProductColor from the Product if it is absent in the form. */
                            foreach ($colorSet as $i => $p) {
                                if ($p->getId() == $id) {
                                    $product->removeProductColor($p);
                                }
                            }
                        }
                    }
                }

                foreach ($colorForm as $i => $p) {
                    // Update ProductColor.
                    $color = $this->handleColors($product, $p);

                    // Update SimilarProductColor.
                    if (!empty($colorForm[$i])) {
                        $color = $this->handleSimilarPC($color, $colorForm[$i]);
                    }

                    // Update Product Images.
                    $color = $this->handleImages($form->all()['colors'][$i], $color);

                    // Delete Image if requested.
                    $color = $this->handleDeleteImage($color, $p['delete-image']);
                }
                // Add updated ProductColor to Product.
                $product->addProductColor($color)->setUpdated();
            }

            // Save Product for the first time.
            $em->persist($product);
            $em->flush();

            // Get Updated timestamps.
            $updated = $product->getUpdated();
            $updatedDC = $product->getProductColor();

            // Group all ProductColors.
            foreach ($updatedDC as $key => $color) {
                $updatedColor[$key] = $updatedDC[$key]->getUpdated();
            }

            // Reverse sort the ProductColors array to get the most recent ProductColor.
            rsort($updatedColor);
            // Get the last ProductColor.
            $lastUpdatedColor = $updatedColor[0];

            /*
            Compare dates of ProductColor with that of
            Product to ensure that any update is reflected in the Product
            $updated column.
            Since the '$updated' attribute of a table is updated only when it detects changes across the whole table, the product above is flushed first to activate the  @ORM\HasLifecycleCallbacks() of $updated Timestamps.
            */
            if ($lastUpdatedColor > $updated) {
                $product = $product->setUpdated();
            }

            // Save Product for the second time with the latest updated timestamps.
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/1_product/edit.html.twig', [
            'product' => $product,
            'colorSelectSet' => $colorSelectSet,
            'colorIdSet' => $colorIdSet,
            'similarPC' => $similarPC,
            'searchForm' => $searchForm,
            'search' => '',
            'form' => $form,
            'colors' => $this->getColors(),
            'fabrics' => $this->getfabrics(),
            'textures' => $this->getTexture(),
            'tags' => $this->getTag(),
            'brands' => $this->brands(),
            'types' => $this->getTypes(),
            'deleteColor' => $checkColor,
            'deleteProduct' => $checkProduct,
            'reviews' => $reviews,
            'sort' => $sort,
        ]);
    }

    /**
     * Process Display Checkbox.
     */
    public function display(Request $r, Product $product, ORM $em, Url $urlGenerator): Response
    {
        // Get Product ID.
        $id = $product->getId();
        // Update parent Product
        $product->setUpdated();

        // Retrieve product ID and update 'displayed' value.
        if (!empty($r->get('product'))) {
            if (!empty($r->get('product')['displayed'])) {
                $product->setDisplayed(true)
                        ->setDateDisplay(new \DateTime());
            }
        } elseif (empty($r->get('product'))) {
            $product->setDisplayed(false);
        }

        // Save Product.
        $em->flush();

        /* Pass the original queries via the the URL,
           so that it returns to that same results.
           Ex.
            http://shoes.store/admin/1_products?brand=crown_vintage
        */
        $options = $r->request->all();
        if (array_key_exists('product', $options)) {
            unset($options['product']);
        }

        /* Remove the page parameter so that it returns to the first page
           showing the entry with the latest update first.
        */
        if (array_key_exists('sort', $options)) {
            if ('pr.updated' == $options['sort']) {
                if (array_key_exists('page', $options)) {
                    unset($options['page']);
                }
            }
        } elseif (!array_key_exists('sort', $options)) {
            if (array_key_exists('page', $options)) {
                unset($options['page']);
            }
        }

        // return $this->redirectToRoute('admin_product_index', $options, Response::HTTP_SEE_OTHER);

        /* Generate a URL with route arguments and with “#” {ID} to
           point to the specific tag. */
        $url = $urlGenerator->generate('admin_product_index', $options);
        $url .= "#$id";

        // Redirect to Admin Index.
        return $this->redirect($url);
    }

    /**
     * Delete Product and its corresponding images.
     */
    public function delete(Request $r, ORM $em, Product $product, ProductRepo $productRepo): Response
    {
        // Validate Csrf Token.
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $r->get('_token'))) {
            $colors = $product->getProductColor();
            foreach ($colors as $i => $color) {
                $id = $color->getId();
                /* Retrieve Products whose 'sort' contains ProductColor ID of
                the current ID. */
                $similarSort = $productRepo->similarSort($id);

                /* Delete the ProductColor IDs from all Products' Similar "sort" */
                if (!empty($similarSort)) {
                    foreach ($similarSort as $i => $similar) {
                        // Get Similar Product.
                        $similarProduct = $similarSort[$i]->getSimilarProduct();
                        $sort = $similarProduct->getSort();

                        // Locate and delete ProductColor ID in sort, if any.
                        if (in_array($id, $sort)) {
                            $findKey = array_search($id, $sort);
                            if (false !== $findKey) {
                                unset($sort[$findKey]);
                            }
                        }

                        $sort = array_values($sort);
                        // Update sort.
                        $similarProduct->setSort($sort);
                        $similarSort[$i]->setSimilarProduct($similarProduct);
                        // Save Product.
                        $productRepo->add($similarSort[$i]);
                    }
                }
                // Delete corresponding Product images.
                $this->deleteFullImagesSet($color);
            }

            // Delete Product.
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Search Product Color for SimilarProductColor and display as search result.
     */
    public function colors(Request $r, ?ProductColor $pc, ColorRepo $colorRepo, SimilarRepo $similarRepo, Search $search): Response
    {
        // Stop search if 'id' parameter is absent.
        if (!$r->query->has('id')) {
            return false;
        }
        // Retrieve 'id' parameter.
        $id = (int) $r->query->get('id');
        // Fetch ProductColor array.
        $resultColor = $colorRepo->fetch($id);
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        // Initialize checkExclude.
        $checkExclude = false;

        // Determine if a ProductColor can be excluded for this Product.
        if (!empty($resultColor) && !empty($pc)) {
            $checkExclude = $colorRepo->checkExclude($pc->getId(), $resultColor['colorId']);
        }

        if (empty($pc)) {
            $pc = '';
        }

        return $this->render('admin/1_product/partials/search/_search.html.twig', [
            'id' => $id,
            'product' => $resultColor,
            'original' => $pc,
            'checkExclude' => $checkExclude,
        ]);
    }

    /**
     * Search Product Color for ExcludeProductColor and display as search result.
     */
    public function colorExclude(Request $r, ColorRepo $colorRepo, SimilarRepo $similarRepo, Search $search): Response
    {
        // Stop search if 'id' parameter is absent.
        if (!$r->query->has('id')) {
            return false;
        }
        // Retrieve 'id' parameter.
        $id = (int) $r->query->get('id');
        // Fetch ProductColor array.
        $resultColor = $colorRepo->fetch($id);

        $exclude = $r->query->all('exclude');
        // Initialize checkProduct.
        $checkProduct = false;
        $checkProduct = (in_array($id, $exclude)) ? true : false;

        // Determine if a ProductColor can be excluded for this Product.
        if (!empty($resultColor) && !empty($pc)) {
            $checkProduct = $colorRepo->checkProduct($pc->getId(), $resultColor['colorId']);
        }

        return $this->render('admin/1_product/partials/search/_search-exclude.html.twig', [
            'id' => $id,
            'product' => $resultColor,
            'checkProduct' => $checkProduct,
        ]);
    }

    /**
     * Search Product and display as search result.
     */
    public function products(Request $r, ?ProductRepo $productRepo, Search $search): Response
    {
        // Create Search form.
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        // Retrieve 'productId' parameter.
        if ($r->query->has('productId')) {
            $id = (int) $r->query->get('productId');
        }
        // Fetch Product array with images.
        $resultProduct = $productRepo->similar($id);

        return $this->render('admin/1_product/partials/search/_search-product.html.twig', [
            'id' => $id,
            'productArray' => $resultProduct,
            'brands' => $this->brands(),
            'colors' => $this->getColors(),
            'categories' => $this->getCategory(),
            'types' => $this->getTypes(),
        ]);
    }

    /**
     * Query video link and display embeded video.
     */
    public function video(Request $r): Response
    {
        // Initialize link.
        $link = '';
        // Retrieve 'videoUrl' parameter.
        if ($r->query->has('product_form')) {
            $form = $r->query->all()['product_form'];
            if (!empty($form['video'])) {
                $link = $form['video']['videoUrl'];
            }

            if (!empty($form['colors'])) {
                $link = $form['colors'][array_key_first($form['colors'])]['video']['videoUrl'];
            }
        }
        // If search query is empty, return empty video template.
        if (empty($link)) {
            return $this->render('admin/1_product/partials/_empty-video.html.twig');
        }

        // Initialize youtube_id.
        $youtube_id = '';
        // Regex expression to validate the URL.
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|live/)|youtu\.be/)([^"&?/ ]{11})%i';
        // Check if URL is a valid Youtube URL.
        if (preg_match($pattern, (string) $link, $matches)) {
            $youtube_id = $matches[1];
        }

        return $this->render('admin/1_product/partials/_video.html.twig', [
            'url' => $youtube_id,
            'link' => $link,
        ]);

        // Option 1
        // 'preg_match_all("'#(?<=v=|v\/|vi=|vi\/|youtu.be\/)[a-zA-Z0-9_-]{11}#'

        // Option 2 (selected)
        // '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|live/)|youtu\.be/)([^"&?/ ]{11})%i'
    }
}
