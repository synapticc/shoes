<?php

// src/Controller/Admin/AdminSettingsController.php

namespace App\Controller\Admin;

use App\Form\User\Settings\AdminThemeForm;
use App\Form\User\Settings\AdminUserInfoForm;
use App\Form\User\Settings\MaxItemsForm;
use App\Repository\User\Settings\MaxItemsRepository as MaxItems;
use Doctrine\ORM\EntityManagerInterface as ORM;
use libphonenumber;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Display various forms on the Admin Settings page and process them on submission.
 */
class AdminSettingsController extends AbstractController
{
    /** Route name: admin_settings,  Path: /admin/settings.
     *
     *  - Display the following forms:
     *  a) Admin User Info Form
     *  b) Admin Theme Form
     *  c) Max Items Form
     *
     *  - Validate each form and update to database.
     */
    public function edit(Request $request, MaxItems $max, ORM $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AdminUserInfoForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminSettings = $form->getData()->setUpdated();
            $validator = libphonenumber\PhoneNumberUtil::getInstance();
            $landline = $adminSettings->getUserPhone()->getLandline();
            $mobile = $adminSettings->getUserPhone()->getMobile();

            /* Validate phone separately using the PHP 'libphonenumber' library. */
            if (!empty($landline)) {
                $isLandlineValid = $validator->isValidNumber($landline);

                if ($isLandlineValid) {
                    $em->persist($adminSettings);
                    $em->flush();

                    return $this->redirectToRoute(
                        'admin_users_index',
                        [],
                        Response::HTTP_SEE_OTHER
                    );
                }

                /* If landline is invalid, flash 'Invalid landline number' error message */
                if (!$isLandlineValid) {
                    $this->addFlash('landline_error', 'Invalid landline number.');
                }

                return $this->redirectToRoute('admin_settings');
            }

            /* Validate mobile separately using the PHP 'libphonenumber' library. */
            if (!empty($mobile)) {
                $isMobileValid = $validator->isValidNumber($mobile);

                if ($isMobileValid) {
                    $em->persist($adminSettings);
                    $em->flush();

                    return $this->redirectToRoute(
                        'admin_users_index',
                        [],
                        Response::HTTP_SEE_OTHER
                    );
                }

                /* If mobile is invalid, flash 'Invalid mobile number' error message */
                if (!$isMobileValid) {
                    $this->addFlash('mobile_error', 'Invalid mobile number.');
                }

                return $this->redirectToRoute('admin_settings');
            }

            $em->persist($adminSettings);
            $em->flush();

            return $this->redirectToRoute('admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        $themeForm = $this->createForm(AdminThemeForm::class, $user);
        $themeForm->handleRequest($request);

        if ($themeForm->isSubmitted() && $themeForm->isValid()) {
            $theme = $themeForm->getData()->setUpdated();
            $em->persist($theme);
            $em->flush();

            return $this->redirectToRoute(
                'admin_users_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $maxItems = $max->findOneBy([], ['updated' => 'DESC']);
        $maxForm = $this->createForm(MaxItemsForm::class, $maxItems);
        $maxForm->handleRequest($request);

        if ($maxForm->isSubmitted() && $maxForm->isValid()) {
            /* The following code should be used only for the first time
               Set creation date:  $maxForm->getData()->setCreated() */
            $maxItems = $maxForm->getData()->setUpdated();
            $em->persist($maxItems);
            $em->flush();

            return $this->redirectToRoute('admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'admin/9_settings/index.html.twig',
            [
                'user' => $user,
                'form' => $form,
                'maxForm' => $maxForm,
                'themeForm' => $themeForm,
            ]
        );
    }
}
