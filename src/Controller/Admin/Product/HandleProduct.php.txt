<?php

// src/Controller/Admin/Product/HandleProduct.php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\Converter\ImageConverter;
use App\Entity\Product\Product\Product;
use App\Entity\Product\Product\SimilarProduct;
use App\Entity\Product\ProductColor\ExcludeColor;
use App\Entity\Product\ProductColor\ExcludeProductColor as ExcludePC;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductColor\ProductColorTags as Tags;
use App\Entity\Product\ProductColor\ProductColorTexture as Texture;
use App\Entity\Product\ProductColor\ProductColorVideo as Video;
use App\Entity\Product\ProductColor\SimilarProductColor as Similar;
use App\Entity\Product\ProductImage\ProductImage;
use App\Entity\Product\ProductImage\ProductImage2;
use App\Entity\Product\ProductImage\ProductImage3;
use App\Entity\Product\ProductImage\ProductImage4;
use App\Entity\Product\ProductImage\ProductImage5;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile as File;

/**
 * Inject methods to handle Product forms after submission.
 */
trait HandleProduct
{
    #[Required]
    public function setRepository(ORM $em): void
    {
        $this->em = $em;
    }

    /**
     * @param array $pc array of Product Color from submitted Form
     *
     * @return ProductColor returns a ProductColor object
     */
    public function handleColors(Product $product, array $pc): ProductColor
    {
        /*
        > Combine the selected colors into one string (a color combination).

        > Rule of color selection
        shoe: shoe refers to sandals, slip-ons and all other footwears.

        The first color is the dominant one of the shoe.
        The second color is the less dominant one.
        The third color refers to patches or traces above or below the shoe.
        ex.
        Black / White => indicates that it is a black shoe with swatches
        of white.
        Stored in database as: black-white
        Black / White / Gold  => indicates that it is a black shoe with swatches
        of white and a few traces of gold (the laces for example).
        Stored in database as: black-white-gold
        */
        $c1 = 'color1';
        $c2 = 'color2';
        $c3 = 'color3';

        if (!empty($pc[$c1]) && empty($pc[$c2]) && empty($pc[$c3])) {
            $pcColor = $pc[$c1];
        } elseif (!empty($pc[$c1]) && !empty($pc[$c2]) && empty($pc[$c3])) {
            $pcColor = $pc[$c1].'-'.$pc[$c2];
        } elseif (!empty($pc[$c1]) && empty($pc[$c2]) && !empty($pc[$c3])) {
            $pcColor = $pc[$c1].'-'.$pc[$c3];
        } elseif (!empty($pc[$c1]) && !empty($pc[$c2]) && !empty($pc[$c3])) {
            $pcColor = $pc[$c1].'-'.$pc[$c2].'-'.$pc[$c3];
        }

        $pcSet = $product->getProductColor();

        foreach ($pcSet as $i => $productColor) {
            if ($pcSet[$i]->getId() == $pc['colorId']) {
                $color = $productColor;
            }
        }

        if (empty($color)) {
            // Create a new ProductColor and assign colors & fabrics.
            $color = new ProductColor();
            $color->setProduct($product)
                  ->setColor($pcColor)
                  ->setFabrics($pc['fabrics']);

            // Assign textures.
            if (!empty($pc['textures']['textures'])) {
                $texture = new Texture();
                $texture->setTextures($pc['textures']['textures']);
                $color->setTextures($texture);
            }

            // Assign tags.
            if (!empty($pc['tags']['tags'])) {
                $tags = new Tags();
                $tags->setTags($pc['tags']['tags']);
                $color->setTags($tags);
            }

            // Create a new ProductColorVideo and assign video URL.
            if (!empty($pc['video']['videoUrl'])) {
                $colorVideo = new Video();
                $colorVideo->setVideoUrl($pc['video']['videoUrl']);
                $color->setVideo($colorVideo);
            }

            // Add the ProductColor to the Product and update the Product.
            $product->addProductColor($color)
                    ->setUpdated();
        } elseif (!empty($color)) {
            // Update colors & fabrics.
            $color->setColor($pcColor)
                  ->setFabrics($pc['fabrics']);

            // Update textures.
            if (!empty($pc['textures']['textures'])) {
                if (!empty($color->getTextures())) {
                    $texture = $color->getTextures();
                } else {
                    $texture = new Texture();
                }

                $texture->setTextures($pc['textures']['textures']);
                $color->setTextures($texture);
            } elseif (empty($pc['textures']['textures'])) {
                if (!empty($color->getTextures())) {
                    $texture = $color->getTextures();
                    $texture->setTextures(null);
                    $color->setTextures($texture);
                }
            }

            // Assign tags.
            if (!empty($pc['tags']['tags'])) {
                if (!empty($color->getTags())) {
                    $tags = $color->getTags();
                } else {
                    $tags = new Tags();
                }

                $tags->setTags($pc['tags']['tags']);
                $color->setTags($tags);
            } elseif (empty($pc['tags']['tags'])) {
                if (!empty($color->getTags())) {
                    $tags = $color->getTags();
                    $tags->setTags(null);
                    $color->setTags($tags);
                }
            }

            // Update video URL.
            if (!empty($pc['video']['videoUrl'])) {
                if (!empty($color->getVideo())) {
                    $colorVideo = $color->getVideo();
                } else {
                    $colorVideo = new Video();
                }

                $colorVideo->setVideoUrl($pc['video']['videoUrl']);
                $color->setVideo($colorVideo);
            } elseif (empty($pc['video']['videoUrl'])) {
                if (!empty($color->getVideo())) {
                    $colorVideo = $color->getVideo();
                    $colorVideo->setVideoUrl(null);
                    $color->setVideo($colorVideo);
                }
            }
        }

        return $color;
    }

    /**
     * @return Product returns a Product object
     */
    public function handleEmptyImages(Product $product): Product
    {
        // Get ProductColor.
        // $pcSet = $product->getProductColor();
        //
        // foreach ($colorToBeDeleted as $i => $color)
        // {
        //   // Fetch ProducColor.
        //   $colorToBeDeleted[$i] = $this->colorRepo->find($color);
        //
        //   // Delete images by whole set.
        //   $this->deleteFullImagesSet($colorToBeDeleted[$i]);
        //   // Update Product.
        //   $product->setUpdated();
        // }

        // // Group all ProductColors.
        // foreach ($pcSet as $i => $pData)
        //   $exclude[$i] = $pData;

        // // Fetch all ProductData of the parent Product.
        // $deleteSet = $this->pdRepo->findBy(
        //                 ['product' => $product,
        //                  'color' => $exclude]);

        // Delete productData when their corresponding colors is deleted
        // if (!empty($deleteSet))
        // {
        //   // Retrieve all OrderItem of the current productData.
        //   $orderExclude = $this->orderItemRepo
        //                 ->findByCart(['products' => $deleteSet]);
        //
        //   if (!empty($orderExclude))
        //   {
        //     // Group ProductData of all OrderItems.
        //     foreach ($orderExclude as $j => $order)
        //       $productDataExclude[$j] = $order->getProductData();
        //
        //     // Compare the two arrays of ProductData and return their difference.
        //     $productDataDelete =
        //       array_values(array_udiff($deleteSet, $productDataExclude,
        //         fn($obj_a, $obj_b) => $obj_a->getId() - $obj_b->getId()));
        //
        //     // Delete the set of ProductData.
        //     if (!empty($productDataDelete))
        //       foreach ($productDataDelete as $key => $productData)
        //         $em->remove($productData);
        //   }
        // }

        // $productColorArray = [];
        // // Group ProductData of all OrderItems.
        // foreach ($productColorSet as $i => $value)
        //   array_push($productColorArray, $value->getId());
        //
        // $colorToBeDeleted =
        // array_diff($productColorSetArray, $productColorArray);
        //
        // foreach ($colorToBeDeleted as $i => $color)
        // {
        //   // Fetch ProducColor.
        //   $colorToBeDeleted[$i] = $this->colorRepo->find($color);
        //
        //   // Delete images by whole set.
        //   $this->deleteFullImagesSet($colorToBeDeleted[$i]);
        //   // Update Product.
        //   $product->setUpdated();
        // }

        return $product;
    }

    /**
     * @param array an array of SimilarProduct derived from submitted
     * Product form
     *
     * @return Product returns a Product object
     */
    public function handleSimilarProduct(Product $product, ?array $pc): Product
    {
        // Create new SimilarProduct or retrieve existing one.
        if (!empty($product->getSimilarProduct())) {
            $similarProduct = $product->getSimilarProduct();
        } else {
            $similarProduct = new SimilarProduct();
            $product->setSimilarProduct($similarProduct);
        }

        /* Check if similar product select are all filled.
            If not, fill them with the parent Product default attributes. */
        if (empty($similarProduct->brands())) {
            $similarProduct->setBrands([$product->getBrand()]);
        }

        if (empty($similarProduct->getOccasions())) {
            $similarProduct->setOccasions([$product->getOccasion()]);
        }

        if (empty($similarProduct->getTypes())) {
            $similarProduct->setTypes([$product->getType()]);
        }

        if (!empty($product->getProductColor())) {
            $colors = [];
            $fabrics = [];
            $textures = [];
            foreach ($product->getProductColor() as $i => $productColor) {
                foreach (explode('-', (string) $productColor->getColor()) as $j => $color) {
                    $colors[] = $color;
                }

                foreach ($productColor->getFabrics() as $j => $fabric) {
                    $fabrics[] = $fabric;
                }

                if (!empty($productColor->getTextures())) {
                    foreach ($productColor->getTextures() as $j => $texture) {
                        $textures[] = $texture;
                    }
                } else {
                    $textures = [];
                }
            }

            if (empty($similarProduct->getColors())) {
                $similarProduct->setColors($colors);
            }

            if (empty($similarProduct->getFabrics())) {
                $similarProduct->setFabrics($fabrics);
            }

            if (empty($similarProduct->getTextures()) && empty($textures)) {
                $similarProduct->setTextures($textures);
            }

            if (!empty($productColor->getTags())) {
                foreach ($productColor->getTags() as $j => $tag) {
                    $tags[] = $tag;
                }
            } else {
                $tags = [];
            }

            if (empty($similarProduct->getTags()) && empty($tags)) {
                $similarProduct->setTags($tags);
            }
        }

        if (!empty($pc['sort'])) {
            /* Convert all product ID (number) into integer */
            foreach ($pc['sort'] as $i => $sort) {
                if (false != filter_var($sort, FILTER_VALIDATE_INT)) {
                    $sortArray[] = filter_var($sort, FILTER_VALIDATE_INT);
                } else {
                    $sortArray[] = $sort;
                }
            }
        } else {
            $sortArray = ['brand-type', 'brand-occasion', 'brand-color', 'color-type', 'color-fabric', 'fabric-type'];
        }

        // Update 'sort' array.
        $similarProduct->setSort($sortArray);

        return $product;
    }

    /**
     * @param array  an array of SimilarProductColor derived from submitted
     * Product form
     *
     * @return ProductColor returns a ProductColor object
     */
    public function handleSimilarPC(ProductColor $productcolor, array $pc): ProductColor
    {
        $odc = 'otherProductColors';
        $opdc = 'otherProductColors';
        $ec = 'excludeColor';
        $epdc = 'excludeProductColors';
        $sm = 'similarProductColor';

        // Create new SimilarProductColor or get existing one.
        if (!empty($productcolor->getSimilarProductColor())) {
            $similarPC = $productcolor->getSimilarProductColor();
        } else {
            $similarPC = new Similar();
        }

        // Create new ExcludeColor or get existing one.
        if (!empty($similarPC->getExcludeColor())) {
            $excludeColor = $similarPC->getExcludeColor();
        } else {
            $excludeColor = new ExcludeColor();
        }

        if (!empty($pc[$sm]['sort'])) {
            /* Convert all product ID (number) into integer */
            foreach ($pc[$sm]['sort'] as $i => $sort) {
                if (false != filter_var($sort, FILTER_VALIDATE_INT)) {
                    $sortArray[] = filter_var($sort, FILTER_VALIDATE_INT);
                } else {
                    $sortArray[] = $sort;
                }
            }

            // Set sort.
            $similarPC->setSort($sortArray);
        } elseif (empty($pc[$sm]['sort'])) {
            $similarPC->setSort([]);
        }

        $colors = [];
        if (!empty($pc[$sm][$ec])) {
            // Set colors.
            $excludeColor->setColors($pc[$sm][$ec]['colors']);
            $similarPC->setExcludeColor($excludeColor);
        } elseif (empty($pc[$sm][$ec])) {
            // Set colors.
            $excludeColor->setColors([]);
            $similarPC->setExcludeColor($excludeColor);
        }

        $colors = [];
        if (!empty($pc[$sm][$epdc])) {
            $colors = $pc[$sm][$epdc];
            $excludeProductColors = [];
            // Group existing excludeProductColors.
            foreach ($similarPC->getExcludeProductColors() as $i => $value) {
                $excludeProductColors[] = $value->getColor()->getId();
            }
            foreach ($colors as $k => $color) {
                $excludeUpdated[] = (int) $color['color'];
            }

            // Determine which ExcludeProductColors to delete.
            $excludeToBeRemoved = array_diff($excludeProductColors, $excludeUpdated);
            // Determine which ExcludeProductColors to add.
            $excludeToBeAdded = array_diff($excludeUpdated, $excludeProductColors);

            // Delete ExcludeProductColors which have been removed.
            if (!empty($excludeToBeRemoved)) {
                foreach ($excludeToBeRemoved as $i => $remove) {
                    foreach ($similarPC->getExcludeProductColors() as $i => $excludeColor) {
                        if ($excludeColor->getColor()->getId() == $remove) {
                            $similarPC->removeExcludeProductColor($excludeColor);
                        }
                    }
                }
            }

            if (!empty($excludeToBeAdded)) {
                foreach ($excludeToBeAdded as $key => $added) {
                    // Create ExcludeProductColor.
                    $excludeColor = new ExcludePC();
                    // Fetch parent ProductColor.
                    $existingColor = $this->colorRepo->find($added);
                    $excludeColor->setColor($existingColor);
                    // Add to SimilarProductColor.
                    $similarPC->addExcludeProductColor($excludeColor);
                }
            }
        } elseif (empty($pc[$sm][$epdc])) {
            // Delete all ProductColors.
            if (!$similarPC->getExcludeProductColors()->isEmpty()) {
                $similarPC->clearExcludeProductColor();
            }
        }
        // Assign to parent ProductColor.
        $productcolor->setSimilarProductColor($similarPC);

        return $productcolor;
    }

    /**
     * @param Form $pc form component object from submitted Product form
     *
     * @return ProductColor returns a ProductColor object
     */
    public function handleImages(Form $pc, ProductColor $color): ProductColor
    {
        // Retrieve uploaded images from respective productColor and save them in $image.
        $i = 'image1';
        $i2 = 'image2';
        $i3 = 'image3';
        $i4 = 'image4';
        $i5 = 'image5';
        if (!empty($pc->get($i)->getData())) {
            $image = $pc->get($i)->getData();
        }

        if (!empty($pc->get($i2)->getData())) {
            $image2 = $pc->get($i2)->getData();
        }

        if (!empty($pc->get($i3)->getData())) {
            $image3 = $pc->get($i3)->getData();
        }

        if (!empty($pc->get($i4)->getData())) {
            $image4 = $pc->get($i4)->getData();
        }

        if (!empty($pc->get($i5)->getData())) {
            $image5 = $pc->get($i5)->getData();
        }

        $product = $color->getProduct();
        // Update Product if any image is updated.
        if (!empty($pc->get($i)->getData())
              || !empty($pc->get($i2)->getData())
              || !empty($pc->get($i3)->getData())
              || !empty($pc->get($i4)->getData())
              || !empty($pc->get($i5)->getData())) {
            $product->setUpdated();
        }

        // Update images.
        if (!empty($image)) {
            $this->uploadToImage($color, $image, null);
        }

        if (!empty($image2)) {
            $this->uploadToImage($color, $image2, 2);
        }

        if (!empty($image3)) {
            $this->uploadToImage($color, $image3, 3);
        }

        if (!empty($image4)) {
            $this->uploadToImage($color, $image4, 4);
        }

        if (!empty($image5)) {
            $this->uploadToImage($color, $image5, 5);
        }

        return $color;
    }

    /**
     * @param array $deleteStatus An array of delete status of each image
     *
     * @return ProductColor Returns a ProductColor object
     */
    public function handleDeleteImage(ProductColor $color, array $deleteStatus): ProductColor
    {
        // Delete existing images individually.
        foreach ($deleteStatus as $deleteSet => $delete) {
            // Update Product if any image is removed.
            if ('' != $delete) {
                $product = $color->getProduct();
                $product->setUpdated();
                $color->setProduct($product);

                switch ($deleteSet) {
                    case 'delete-image1': $image = null;
                        break;
                    case 'delete-image2': $image = 2;
                        break;
                    case 'delete-image3': $image = 3;
                        break;
                    case 'delete-image4': $image = 4;
                        break;
                    case 'delete-image5': $image = 5;
                        break;
                }
                $this->setImagePathToNull($color, null, $image);
            }
        }

        return $color;
    }

    /**
     * Replace the image path to null.
     * Delete the images from the server.
     *
     * @param File|null $image       image file uploaded by the user through the Product form
     * @param int|null  $imageNumber the image index
     *
     * @return ProductColor $color
     */
    public function setImagePathToNull(ProductColor $color, ?File $image, ?int $imageNumber): ProductColor
    {
        $filesystem = new Filesystem();
        $current_dir_path = getcwd();
        $getProductImage = 'getProductImage'.$imageNumber;

        // Fetch image paths.
        $imagePath = $color->$getProductImage()->getImage();
        $imageMediumPath = $color->$getProductImage()->getImageMedium();
        $imageSmallPath = $color->$getProductImage()->getImageSmall();

        if ($imagePath and $imageMediumPath and $imageSmallPath) {
            // Create an array of links of images to be deleted.
            $imageToRemove = [
                $current_dir_path.'/'.$imagePath,
                $current_dir_path.'/'.$imageMediumPath,
                $current_dir_path.'/'.$imageSmallPath,
            ];

            // Delete image files.
            try {
                $filesystem->remove($imageToRemove);
            } catch (IOExceptionInterface $exception) {
                echo 'Error deleting directory at'.$exception->getPath();
            }

            // Update image path in database.
            if (empty($image)) {
                $productImage = $color->$getProductImage();
                $setProductImage = 'setProductImage'.$imageNumber;
                $color->$setProductImage(null);
            }
        }

        return $color;
    }

    /**
     * Delete entire set of images from the server.
     */
    public function deleteFullImagesSet(ProductColor $color): void
    {
        // Get current directory path.
        $filesystem = new Filesystem();
        $current_dir_path = getcwd();

        for ($i = 1; $i < 6; ++$i) {
            $imageNumber = (1 == $i) ? null : $i;
            $getProductImage = 'getProductImage'.$imageNumber;

            if (!empty($color->$getProductImage())) {
                // Retrieve image paths.
                $imagePath = $color->$getProductImage()->getImage();
                $imagePath = implode('/', array_slice(explode('/', $imagePath), 0, 4));
                if (!empty($imagePath)) {
                    $imageToRemove = [
                        $current_dir_path.'/'.$imagePath,
                    ];

                    // Delete binary images files.
                    try {
                        $filesystem->remove($imageToRemove);
                    } catch (IOExceptionInterface $exception) {
                        echo 'Error deleting directory at'.$exception->getPath();
                    }
                }
            }
        }
    }

    /**
     * Resize uploaded images using ImageConverter
     * Persist the path of each of theses images to ProductImage table.
     *
     * @param File|null $image       image file uploaded by the user through the Product form
     * @param int|null  $imageNumber the image index
     *
     * @return ProductColor $color
     */
    public function uploadToImage(ProductColor $color, ?File $image, ?int $imageNumber)
    {
        $converter = new ImageConverter();
        $product = $color->getProduct();
        //  Resize and save images physically.
        $file = $converter->resizer($image, $product, $this->slug);
        $imageURL = $file['largeName'];
        $imageMediumURL = $file['mediumName'];
        $imageSmallURL = $file['smallName'];
        $originalName = $file['originalName'];
        $imageNameOnly = $file['imageNameOnly'];

        switch ($imageNumber) {
            case 2:
                $getProductImage = 'getProductImage2';
                $setProductImage = 'setProductImage2';
                break;
            case 3:
                $getProductImage = 'getProductImage3';
                $setProductImage = 'setProductImage3';
                break;
            case 4:
                $getProductImage = 'getProductImage4';
                $setProductImage = 'setProductImage4';
                break;
            case 5:
                $getProductImage = 'getProductImage5';
                $setProductImage = 'setProductImage5';
                break;
            default:
                $getProductImage = 'getProductImage';
                $setProductImage = 'setProductImage';
                break;
        }

        // Create new ProductImage and set image paths.
        if (empty($color->$getProductImage())) {
            $productImage = match ($imageNumber) {
                2 => new ProductImage2(),
                3 => new ProductImage3(),
                4 => new ProductImage4(),
                5 => new ProductImage5(),
                default => new ProductImage(),
            };

            $productImage
              ->setImage($imageURL)
              ->setImageMedium($imageMediumURL)
              ->setImageSmall($imageSmallURL)
              ->setImageOriginal($originalName)
              ->setImageNameOnly($imageNameOnly);
        }
        // Update existing ProductImage.
        elseif (!empty($color->$getProductImage())) {
            $productImage = $color->$getProductImage();
            $productImage
              ->setImage($imageURL)
              ->setImageMedium($imageMediumURL)
              ->setImageSmall($imageSmallURL)
              ->setImageOriginal($originalName)
              ->setImageNameOnly($imageNameOnly);
        }

        // Assign to parent ProductColor.
        $color->$setProductImage($productImage);

        return $color;
    }
}
