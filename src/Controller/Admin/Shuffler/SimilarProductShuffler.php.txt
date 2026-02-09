<?php

// src/Controller/Admin/Shuffler/SimilarProductShuffler.php

namespace App\Controller\Admin\Shuffler;

use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductData\ProductData;
use App\Repository\Product\Product\ProductRepository;
use App\Repository\Product\ProductData\ProductDataRepository;

/**
 * The Shuffler class was used before the SimilarProduct
 * table was created, where the specific criteria for
 * similar products can be configured and saved in the
 * database.
 *
 * Retrieve productData based on similar:
 * a) brand
 * b) occasion
 * c) type
 * d) category
 * Shuffle the final array to create randomness in the list displayed.
 *
 * @param array                 $productDatabySize,
 * @param ProductData           $productData
 * @param ProductRepository     $productRepository,
 * @param ProductDataRepository $productDataRepository
 *
 * @property string $brand
 * @property string $category
 * @property string $type
 * @property string $occasion
 * @property array productDataByBrand
 * @property array                 $productDataByOccasion
 * @property array                 $productDataByType
 * @property array                 $productDatabySize
 * @property array                 $productDataByOccasion
 * @property Product               $product
 * @property ProductData           $productData
 * @property ProductRepository     $productRepository
 * @property ProductDataRepository $productDataRepository
 *
 * @method similarBrand() return array
 * @method similarOccasion() return array
 * @method similarType() return array
 * @method shuffle() return array
 */
class SimilarProductShuffler
{
    /**
     * The brand of the product listed.
     *
     * @var string
     */
    private $brand;
    /**
     * The category of the product listed.
     *
     * @var string
     */
    private $category;
    /**
     * The type of the product listed.
     *
     * @var string
     */
    private $type;
    /**
     * The occasion of the product listed.
     *
     * @var string
     */
    private $occasion;
    /**
     * Array of similar productData by brand.
     *
     * @var array
     */
    private $productDataByBrand;
    /**
     * Array of similar productData by occasion.
     *
     * @var array
     */
    private $productDataByOccasion;
    /**
     * Array of similar productData by type.
     *
     * @var array
     */
    private $productDataByType;
    /**
     * The occasion of the product listed.
     *
     * @var array
     */
    private $productDataSet;
    /**
     * The product (parent) of the productData listed.
     */
    private readonly Product $product;
    /**
     * The productData listed.
     */
    private readonly ProductData $productData;

    public function __construct(
        /**
         * An array of all productData with the same color.
         * Ex. All 'Black' productData of 'Adidas CF Sport'.
         * Set the product size as the keys.
         */
        private readonly array $productDatabySize,
        ProductData $productData,
        /**
         * The product repository.
         */
        private readonly ProductRepository $productRepository,
        /**
         * The productData repository.
         */
        private readonly ProductDataRepository $productDataRepository,
    ) {
        $this->product = $productData->getProduct();
        $this->productData = $productData;
        $this->brand = $this->product->getBrand();
        $this->category = $this->product->getCategory();
        $this->type = $this->product->getType();
        $this->occasion = $this->product->getOccasion();
    }

    /**
     * Retrieving images of productData with highest quantity.
     *
     * @return array $productDataBy
     */
    private function findMaxQuantity(
        array $productByAll,
    ) {
        if ($productByAll) {
            for ($i = 0; $i < \count($productByAll); ++$i) {
                $id[$i] = $productByAll[$i]->getId();
                $productDataByBrandAll[$i] =
                $this->productDataRepository
                    ->findBy(
                        ['product' => $id[$i]]
                    );

                if (!empty($productDataByBrandAll[$i])) {
                    for ($j = 0; $j < \count($productDataByBrandAll[$i]); ++$j) {
                        if ($productDataByBrandAll[$i][$j]->getColor()) {
                            $productColor[$i][$j] = $productDataByBrandAll[$i][$j]
                                                      ->getColor()->getColor();

                            $productColor[$i] = array_values($productColor[$i]);

                            if ($productDataByBrandAll[$i][$j]->getColor()) {
                                if ($productDataByBrandAll[$i][$j]->getQtyInStock()) {
                                    $quantityAll[$i][$productColor[$i][$j]][] =
                                    $productDataByBrandAll[$i][$j]->getQtyInStock();
                                }
                            }

                            // retrieve all images & group by color
                            $productDataByAll[$i][$productColor[$i][$j]] =
                            $productDataByBrandAll[$i][$j];
                        }
                    }
                }
            }
        }

        // resetting array keys
        if (in_array(null, $productDataByBrandAll)) {
            foreach ($quantityAll as $key => $value) {
                if (1 == count($quantityAll)) {
                    $quantityAll = [];
                    $quantityAll[0] = $value;
                }
            }
        }

        if (!empty($quantityAll)) {
            $quantityAll = array_values($quantityAll);
            $productDataByAll = array_values($productDataByAll);

            foreach ($quantityAll as $kColor => $color) {
                foreach ($color as $key => $value) {
                    $quantityAll[$kColor][$key] = array_sum($value);
                }

                // locating element with highest value
                $maxQty = max($quantityAll[$kColor]);
                $keyMax[$kColor] = array_search($maxQty, $quantityAll[$kColor]);

                $productDataBy[$kColor] = $productDataByAll[$kColor][$keyMax[$kColor]];
            }
        } elseif (empty($quantityAll)) {
            $this->productDataBy = null;
        }

        return [
            'productData' => $productDataBy,
        ];
    } // end of findMaxQuantity()

    /**
     * Retrieving images of productData with highest quantity.
     *
     * @return array $productDataBy
     */
    private function findMaxColorQuantity(
        array $products,
    ) {
        if (!empty($products)) {
            foreach ($products as $i => $product) {
                $productDataByColor[$product->getColor()->getId()][$product->getId()] =
                $product;
                if (!empty($product->getQtyInStock())) {
                    $quantityAll[$product->getColor()->getId()][$product->getId()] =
                      $product->getQtyInStock();
                }
            }
        }

        // Grouping $productData by respective color
        if (!empty($quantityAll)) {
            foreach ($quantityAll as $qKey => $qValue) {
                // locating element with highest value
                $maxQty[$qKey] = max($quantityAll[$qKey]);
                $keyMax[$qKey] = array_search($maxQty[$qKey], $quantityAll[$qKey]);
                $productDataByColorSet[$qKey] = $productDataByColor[$qKey][$keyMax[$qKey]];
            }

            return $productDataByColorSet;
        }

        return false;
    } // end of findMaxColorQuantity()

    /**
     * Remove a specific array of ProductData from a given array.
     *
     * @param array $sourceArray The original array of ProductData
     * @param array $toBeRemoved The set of productData to be removed
     *                           the original array
     *
     * @return array $sourceArray
     */
    public function removeFromArray(
        array $sourceArray,
        array $toBeRemoved,
    ) {
        for ($i = 0; $i < count($toBeRemoved); ++$i) {
            if (in_array($toBeRemoved[$i], $sourceArray)) {
                $productDataKey =
                array_search($toBeRemoved[$i], $sourceArray);
                unset($sourceArray[$productDataKey]);
                $sourceArray = array_values($sourceArray);
            }
        }

        return $sourceArray;
    }

    /**
     * Retrieve productData based on similar brand.
     *
     * @return array $this->productDataByBrand;
     */
    public function similarBrand(): array
    {
        // retrieve all productData by similar brand for
        // the same category (i.e. Men, Women or Kids).
        $productByBrandAll = $this->productRepository->findBy([
            'brand' => $this->brand,
            'category' => $this->category,
        ]);

        if (!empty($productByBrandAll)) {
            $this->productDataByBrand =
            $this->findMaxQuantity($productByBrandAll);
        }

        return $this->productDataByBrand;
    }

    /**
     * Retrieve productData based on similar occasion
     * Ex.: Casual, Dress, Formal, Outdoor, Office.
     *
     * @return array $this->productDataByOccasion;
     */
    public function similarOccasion(): array
    {
        // retrieve by similar occasion
        $productByOccasionAll = $this->productRepository->findBy([
            'category' => $this->category,
            'occasion' => $this->occasion,
        ]);

        if (!empty($productByOccasionAll)) {
            $this->productDataByOccasion =
            $this->findMaxQuantity($productByOccasionAll);
        }

        return $this->productDataByOccasion;
    }

    /**
     * Retrieve productData based on similar type
     * Ex.: Sandals, Heels, Pumps, Slip-Ons, Slippers.
     *
     * @return array $this->productDataByType;
     */
    public function similarType(): array
    {
        // retrieve by similar type
        $productByTypeAll = $this->productRepository->findBy([
            'category' => $this->category,
            'type' => $this->type,
        ]);

        if (!empty($productByTypeAll)) {
            $this->productDataByType =
            $this->findMaxQuantity($productByTypeAll);
        }

        return $this->productDataByType;
    }

    /**
     * Step 1:
     * Combine the following arrays:
     * a) $productDataByBrand,
     * b) $productDataByOccasion,
     * c) $productDataByType,
     * d) $productDataSet
     *
     * Step 2: Remove any productData duplicates.
     *
     * Step 3: Remove the original productData so that
     * the main product doesn't appear in similar product
     * array as well.
     *
     * Step 4: Shuffle the combined arrays and return the final array of
     * similar products.
     *
     * @param array [
     *   'brand' => true,
     *   'occasion' => true,
     *   'type' => false,
     * ],
     *
     * @return array $similarProduct;
     */
    public function shuffle(
        array $options = [
            'brand' => true,
            'occasion' => true,
            'type' => true, ],
    ): array {
        $productDataByBrand = $this->similarBrand()['productData'];
        $productDataByOccasion = $this->similarOccasion()['productData'];
        $productDataByType = $this->similarType()['productData'];

        $similarProduct = [];
        $similarProduct = $this->productDataRepository->similar($this->productData);

        if (true == $options['brand']) {
            $similarProduct =
            array_merge($similarProduct, $productDataByBrand);
        }

        if (true == $options['occasion']) {
            $similarProduct =
            array_merge($similarProduct, $productDataByOccasion);
        }

        if (true == $options['type']) {
            $similarProduct =
            array_merge($similarProduct, $productDataByType);
        }

        // Excluding similar productData by brand, occasion or
        // type in case they are set to false.
        if (false == $options['brand']) {
            if (!empty($productDataByBrand)) {
                $similarProduct =
                $this->removeFromArray(
                    $similarProduct,
                    $productDataByBrand
                );
            }
        }

        if (false == $options['occasion']) {
            if (!empty($productDataByOccasion)) {
                $similarProduct =
                $this->removeFromArray(
                    $similarProduct,
                    $productDataByOccasion
                );
            }
        }

        if (false == $options['type']) {
            if ($productDataByBrand) {
                $similarProduct =
                $this->removeFromArray(
                    $similarProduct,
                    $productDataByType
                );
            }
        }

        // removing duplicate (id)
        if (!empty($similarProduct)) {
            for ($i = 0; $i < count($similarProduct); ++$i) {
                $similarProductById[$i] = $similarProduct[$i]->getId();
            }

            // reset array keys & removing duplicates
            $similarProductById = array_values(
                array_unique($similarProductById)
            );

            // removing duplicate color
            // i.e. removing other productData which has the same
            // color even though they have different sizes.
            if (!empty($similarProduct)) {
                // create an array with id as key and color combination
                // as value.
                $productColor = [];
                for ($z = 0; $z < count($similarProduct); ++$z) {
                    $productColor[$z] = $similarProduct[$z]
                                              ->getColor()->getId();

                    $productColor = array_values($productColor);

                    $productColorSet[$similarProduct[$z]->getId()] =
                    $productColor[$z];
                }

                $uniqueSimilarProduct = array_unique($productColorSet);
                $uniqueSimilarProduct = array_keys($uniqueSimilarProduct);
                // reccover productData from repository using their id
                for ($m = 0; $m < count($uniqueSimilarProduct); ++$m) {
                    $uniqueSimilarProduct[$m] =
                    $this->productDataRepository
                      ->findOneBy(['id' => $uniqueSimilarProduct[$m]]);
                }
            } elseif (empty($similarProduct)) {
                $uniqueSimilarProduct = [];
            }
        }

        shuffle($uniqueSimilarProduct);

        return [
            'similarProduct' => $uniqueSimilarProduct,
        ];
    } // end of shuffle() function
} // end of SimilarProductShuffler class
