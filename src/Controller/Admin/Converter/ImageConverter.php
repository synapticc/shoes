<?php

// src/Controller/Admin/Converter/ImageConverter.php

namespace App\Controller\Admin\Converter;

use App\Entity\Product\Product\Product;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile as File;
use Symfony\Component\String\Slugger\SluggerInterface as Slug;

/**
 * Resize original images into large, medium and small and keep an original copy.
 * Steps:
 *   1) Sanitize the image names.
 *   2) Rename images and set their directory according to their sizes.
 *   3) Create the respective directories.
 *   4) Copy the newly uploaded images to their directories.
 *
 * @property string $largeName
 * @property string $mediumName
 * @property string $smallName
 * @property string $originalName
 * @property string $imageNameOnly
 *
 * @method resizer() return array
 */
class ImageConverter
{
    private string $largeName;
    private string $mediumName;
    private string $smallName;
    private string $originalName;
    private string $imageNameOnly;

    /**
     * Resize images.
     *
     * @return array[] returns an associative array of image file names
     */
    public function resizer(?File $image, Product $product, Slug $slug)
    {
        $name = $slug->slug(strtolower($product->getName()));
        $brand = $slug->slug(strtolower($product->getBrand()));
        $category = $slug->slug(strtolower($product->getCategory()));

        // Retrieve the original image name
        $originalName =
          pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

        $safeName = substr($slug->slug($originalName), 0, 10);
        $imageWidth = getimagesize($image)[0];
        $imageHeight = getimagesize($image)[1];

        $imageMediumHeight = 300;
        $imageMediumWidth =
          floor($imageMediumHeight / ($imageHeight / $imageWidth));

        $imageSmallWidth = floor($imageMediumWidth / 4);
        $imageSmallHeight = floor($imageMediumHeight / 4);

        /* Example of URL to be generated:
          uploads/wolverine/men/floorhand-steel-toe/large/2-65c33a42bc11c-1280x960.webp
        */
        $this->largeName =
          $safeName.'-'.uniqid().'-'.$imageWidth.'x'.$imageHeight.'.webp';

        /* Example of URL to be generated:
          uploads/wolverine/men/floorhand-steel-toe/medium/2-65c33a42bc140-400x300.webp
        */
        $this->mediumName =
          $safeName.'-'.uniqid().
          '-'.$imageMediumWidth.'x'.$imageMediumHeight.'.webp';

        /* Example of URL to be generated:
          uploads/wolverine/men/floorhand-steel-toe/small/2-65c33a42bc14e-100x75.webp
        */
        $this->smallName =
          $safeName.'-'.uniqid().
          '-'.$imageSmallWidth.'x'.$imageSmallHeight.'.webp';

        $this->originalName =
          $image->getClientOriginalName().' ('.date("l jS \of F Y H:i:s", time()).')';

        $this->imageNameOnly = "$safeName".'-'.uniqid().'.webp';

        $filesystem = new Filesystem();
        $current_dir_path = getcwd();
        $filename = $image;
        $extension = strtolower((string) $image->guessExtension());

        // Create new directory
        try {
            /* Original image */
            // New name folder
            $new_dir_path = $current_dir_path."/uploads/_original/products/$brand/$category/$name";
            // New path name
            $new_file_path = $current_dir_path."/uploads/_original/products/$brand/$category/$name/$this->originalName";
            // Path to be stored in Database
            $this->originalName = "uploads/_original/products/$brand/$category/$name/$this->originalName";

            if (!$filesystem->exists($new_dir_path)
                || !$filesystem->exists($new_file_path)) {
                $old = umask(0);

                $filesystem->mkdir($new_dir_path, 0775);
                $filesystem->chown($new_dir_path, 'www-data');
                $filesystem->chgrp($new_dir_path, 'www-data');
                $filesystem->copy($image, $new_file_path);

                umask($old);
            }

            /*  Large image */
            // New name folder
            $new_dir_path = $current_dir_path."/uploads/$brand/$category/$name/large";
            // New path name
            $new_file_path = $current_dir_path."/uploads/$brand/$category/$name/large/$this->largeName";
            // Path to be stored in Database
            $this->largeName = "uploads/$brand/$category/$name/large/$this->largeName";

            if (!$filesystem->exists($new_dir_path)
                || !$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->mkdir($new_dir_path, 0775);
                $filesystem->chown($new_dir_path, 'www-data');
                $filesystem->chgrp($new_dir_path, 'www-data');
                $filesystem->copy($image, $new_file_path);
                umask($old);

                if ('gif' == $extension) {
                    $source = imagecreatefromgif($filename);
                } elseif ('png' == $extension) {
                    $source = imagecreatefrompng($filename);
                } elseif ('jpg' == $extension || 'jpeg' == $extension) {
                    $source = imagecreatefromjpeg($filename);
                }

                // imagejpeg($destination, $new_file_path, 100);
                [$width, $height] = getimagesize($filename);
                $destination = imagecreatetruecolor($width, $height);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                imagewebp($destination, $new_file_path, 50);
                imagedestroy($source);
                imagedestroy($destination);
            }

            /*  Medium image */
            // New name folder
            $new_dir_path = $current_dir_path."/uploads/$brand/$category/$name/medium";
            // New path name
            $new_file_path = $current_dir_path."/uploads/$brand/$category/$name/medium/$this->mediumName";
            // Path to be stored in Database
            $this->mediumName = "uploads/$brand/$category/$name/medium/$this->mediumName";

            if (!$filesystem->exists($new_dir_path)
                || !$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->mkdir($new_dir_path, 0775);
                $filesystem->chown($new_dir_path, 'www-data');
                $filesystem->chgrp($new_dir_path, 'www-data');
                $filesystem->copy($image, $new_file_path);
                umask($old);

                if ('gif' == $extension) {
                    $source = imagecreatefromgif($filename);
                } elseif ('png' == $extension) {
                    $source = imagecreatefrompng($filename);
                } elseif ('jpg' == $extension || 'jpeg' == $extension) {
                    $source = imagecreatefromjpeg($filename);
                }

                [$width, $height] = getimagesize($filename);
                $newwidth = $imageMediumWidth;
                $newheight = $imageMediumHeight;
                $destination = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                // imagejpeg($destination, $new_file_path, 100);
                imagewebp($destination, $new_file_path, 70);
                imagedestroy($source);
                imagedestroy($destination);
            }

            /*  Small image */
            // New name folder
            $new_dir_path = $current_dir_path."/uploads/$brand/$category/$name/small";
            // New path name
            $new_file_path = $current_dir_path."/uploads/$brand/$category/$name/small/$this->smallName";
            // Path to be stored in Database
            $this->smallName = "uploads/$brand/$category/$name/small/$this->smallName";

            if (!$filesystem->exists($new_dir_path)
                || !$filesystem->exists($new_file_path)) {
                $old = umask(0);
                $filesystem->mkdir($new_dir_path, 0775);
                $filesystem->chown($new_dir_path, 'www-data');
                $filesystem->chgrp($new_dir_path, 'www-data');
                $filesystem->copy($image, $new_file_path);
                umask($old);

                if ('gif' == $extension) {
                    $source = imagecreatefromgif($filename);
                } elseif ('png' == $extension) {
                    $source = imagecreatefrompng($filename);
                } elseif ('jpg' == $extension || 'jpeg' == $extension) {
                    $source = imagecreatefromjpeg($filename);
                }

                [$width, $height] = getimagesize($filename);
                $newwidth = $imageSmallWidth;
                $newheight = $imageSmallHeight;
                $destination = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagewebp($destination, $new_file_path, 100);
                imagedestroy($source);
                imagedestroy($destination);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        $fileName = [
            'largeName' => $this->largeName,
            'smallName' => $this->smallName,
            'mediumName' => $this->mediumName,
            'originalName' => $this->originalName,
            'imageNameOnly' => $this->imageNameOnly,
        ];

        return $fileName;
    }
}
