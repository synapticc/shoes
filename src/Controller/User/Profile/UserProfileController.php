<?php

// src/Controller/User/Profile/UserProfileController.php

namespace App\Controller\User\Profile;

use App\Controller\_Utils\Attributes;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Controller\User\Deactivation\AccountDeactivation;
use App\Entity\User\UserImage;
use App\Form\User\UserProfileBillingForm;
use App\Form\User\UserProfileForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Display, edit and process User Profile form.
 *
 * Path: /profile
 */
class UserProfileController extends AbstractController
{
    use Cart;
    use Attributes;

    /**
     * Edit User Profile form.
     */
    public function edit(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, AccountDeactivation $deactivate): Response
    {
        if ($this->getUser()) {
            // Assign logged user to variable $user
            $user = $this->getUser();
            $user_id = $this->getUser()->getId();

            $email = $user->getEmail();
            $password = $user->getPassword();

            // Retrieve cart items and cart product images
            $this->cart($user, null);

            /* Verify if the page is being redirected from billing page
             following which firstName and lastName field should be mandatory. */
            $billing = $request->query->get('billing');
            $billingCart = $request->query->get('id');

            if (!empty($billing)) {
                $form = $this->createForm(UserProfileBillingForm::class, $user);
            } elseif (empty($billing)) {
                $form = $this->createForm(UserProfileForm::class, $user);
            }

            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            /* Retrieve the city manually since the cities in <select> have been
            added dynamically after loading the page and are not recognized
            by handleRequest.
            */

            if (!empty($request->request->all()['user_profile_form']['userAddress']['city'])) {
                $city = $request->request->all()['user_profile_form']['userAddress']['city'];
                $userAddress = $user->getUserAddress();
                $userAddress = $userAddress->setCity($city);
                $user->setUserAddress($userAddress);
            }

            $uuid = $user->getUuid();
            $imageFile = $form->get('image')->getData();
            $filesystem = new Filesystem();
            $current_dir_path = getcwd();

            // delete existing images
            if (true == $request->get('delete-image')) {
                $imagePath = $user->getUserImage()->getImage();

                if (!empty($imagePath)) {
                    // extract path from URL
                    $exploded = explode('/', (string) $imagePath);
                    $imagePath = str_replace($exploded[4], '', $imagePath);

                    $imageToRemove = [
                        $current_dir_path.'/'.$imagePath,
                    ];

                    try {
                        // remove binary images files
                        $filesystem->remove($imageToRemove);
                    } catch (IOExceptionInterface $exception) {
                        echo 'Error deleting directory at'.$exception->getPath();
                    }

                    // edit entry in product_images table
                    if (empty($imageFile)) {
                        $userImage = $user->getUserImage();
                        $userImage->setImage(null);
                        $user->setUserImage($userImage);
                    }
                }
            }

            // handle images
            if (!empty($imageFile)) {
                $originalFilename =
                  pathinfo(
                      (string) $imageFile->getClientOriginalName(),
                      PATHINFO_FILENAME
                  );

                $safeName = $slugger->slug($originalFilename);
                $filename = $imageFile;
                $extension = strtolower((string) $imageFile->guessExtension());
                $safeName = $safeName.'_'.date(time()).'.'.$extension;

                // delete existing images to add new image
                if ($user->getUserImage()) {
                    $userImage = $user->getUserImage();
                    $imagePath = $user->getUserImage()->getImage();

                    if (!empty($imagePath)) {
                        // extract path from URL
                        $exploded = explode('/', (string) $imagePath);
                        $imagePath = str_replace($exploded[4], '', $imagePath);

                        $imageToRemove = [
                            $current_dir_path.'/'.$imagePath,
                        ];

                        try {
                            // remove binary images files
                            $filesystem->remove($imageToRemove);
                        } catch (IOExceptionInterface $exception) {
                            echo 'Error deleting directory at'.$exception->getPath();
                        }

                        // edit entry in user_image table
                        $userImage->setImage(null);
                        $user->setUserImage($userImage);
                    }
                } elseif (empty($user->getUserImage())) {
                    $userImage = new UserImage();
                }

                // Make a new directory & copy new image
                try {
                    // Store a backup of the original image
                    // New path name
                    $new_file_path = $current_dir_path."/uploads/_original/users/$uuid/profile/$safeName";

                    if (!$filesystem->exists($new_file_path)) {
                        $old = umask(0);
                        $filesystem->copy($imageFile, $new_file_path);
                        umask($old);
                    }

                    // Store image to be displayed
                    $imageURL = "uploads/users/$uuid/profile/$safeName";
                    $new_file_path = $current_dir_path.'/'.$imageURL;

                    if (!$filesystem->exists($new_file_path)) {
                        $old = umask(0);
                        $filesystem->copy($imageFile, $new_file_path);
                        umask($old);
                    }
                } catch (IOExceptionInterface $exception) {
                    echo 'Error creating directory at'.$exception->getPath();
                }

                $userImage->setImage($imageURL);
                $user->setUserImage($userImage);
            }

            $em->persist($user);
            $em->flush();

            if (!empty($billing) && !empty($billingCart)) {
                return $this->redirectToRoute('store_billing', [
                    'paidOrder' => $billingCart,
                ], Response::HTTP_SEE_OTHER);
            } elseif (empty($billing)) {
                return $this->redirectToRoute('store', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('store', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form,
            'cart' => $this->cart,
        ]);
    }
}
