<?php

// src/Controller/Store/StoreAllController.php

namespace App\Controller\Store;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\Product\ProductData\ProductData2Repository as Data2Repo;
use App\Repository\Product\ProductData\ProductDataRepository as DataRepo;
use App\Repository\User\Settings\MaxItemsRepository as MaxItems;
use Fuse\Fuse;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreAllController extends AbstractController
{
    use Cart;
    use Attributes;

    public function index(Request $r, DataRepo $repo, MaxItems $max, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;
        $productData = $repo->filter($q);

        $count['brand'] = '';
        $count['category'] = '';
        $count['occasion'] = '';
        $count['type'] = '';
        $count['fabrics'] = '';
        $count['textures'] = '';
        $count['color'] = '';

        /* Determine the count of each filter categories */
        if (!empty($productData)) {
            $textures = [];

            foreach ($productData as $i => $product) {
                $brands[] = $product['brand'];
                $categories[] = $product['category'];
                $occasions[] = $product['occasion'];
                $types[] = $product['type'];
                $fabrics[] = $product['fabrics'];
                $textures[] = !empty($product['textures']) ? $product['textures'] : [];
                $productColors[] = $product['colors_set'];
            }

            foreach ($productColors as $i => $dataColor) {
                foreach ($dataColor as $j => $color) {
                    $colors[] = $color;
                }
            }

            $count['brand'] = array_count_values($brands);
            $count['category'] = array_count_values($categories);
            $count['occasion'] = array_count_values(array_merge(...$occasions));
            $count['type'] = array_count_values($types);
            $count['fabrics'] = array_count_values(array_merge(...$fabrics));
            $count['textures'] = !empty($textures) ? array_count_values(array_merge(...$textures)) : '';
            $count['color'] = array_count_values($colors);
        }

        $filter = $r->query->all();

        /* Assign default
          - minimum and maximum price (price range)
          - the sorting order of products
        */
        if (empty($filter['price'])) {
            $filter['price']['min'] = 500;
            $filter['price']['max'] = 25000;
            $filter['price']['order'] = 'nameAsc';
        } elseif (empty($filter['price']['order'])) {
            $filter['price']['order'] = 'nameAsc';
        }

        /* If price range is set, use its lowest value
           as the minimum price and its highest value as
           the maximum price
        */
        if (!empty($filter['price_range'])) {
            foreach ($filter['price_range'] as $i => $priceValue) {
                $priceRangeArr[$i] = explode('_', $priceValue);
            }

            $priceRangeArr = array_values($priceRangeArr);
            $priceValues = array_merge(...$priceRangeArr);
            sort($priceValues);

            $minPrice = $priceValues[0];
            $maxPrice = $priceValues[array_key_last($priceValues)];

            $filter['price']['min'] = $minPrice;
            $filter['price']['max'] = $maxPrice;
        }
        /* Retrieve maximum items for listing (latest entry in MaxItems) */
        $maxItems = $max->listing();
        $page = $q->getInt('page', 1);
        $productData = $pg->paginate($productData, $page, $maxItems);

        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        $brandShow = array_slice($this->brands(), 0, 5);
        $brandHidden = array_slice($this->brands(), 5, count($this->brands()));

        $options =
        ['productData' => $productData,
            'get' => $filter,
            'searchForm' => $searchForm,
            'cart' => $this->cart,
            'brandShow' => $brandShow,
            'brandHidden' => $brandHidden,
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
            'sorting' => $this->getSorting(),
            'count' => $count,
        ];

        return $this->render('store/index.html.twig', $options);
    }

    public function search(Request $r, Data2Repo $repo): Response
    {
        $q = $r->query;
        $search = $q->has('q') ? $q->get('q') : '';
        $results = [];
        $products = [];

        if (!empty($search)) {
            $products = $repo->instantSearch($search);

            $attributes = $this->getAttributeSet();
            if (in_array($search, $attributes)) {
                $attribute = array_search($search, $attributes);
            } else {
                $attribute = [];
            }

            $options = [
                'keys' => ['name', 'alias', 'fullName'],
                'includeScore' => true,
                'includeMatches' => true,
                'findAllMatches' => true,
                'threshold' => 0.1];

            $fuse = new Fuse($this->getAttributeSet(), $options);
            $results = $fuse->search($search);

            return $this->render(
                'store/partials/search.html.twig',
                [
                    'results' => $results,
                    'products' => $products,
                    'brands' => $this->brands(),
                ]
            );
        }

        /* Return empty */
        return new Response('');
    }
}
