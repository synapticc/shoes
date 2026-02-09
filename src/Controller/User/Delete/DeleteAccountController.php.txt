<?php

// src/Controller/User/Delete/DeleteAccountController.php

namespace App\Controller\User\Delete;

use App\Entity\User\UserDelete;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as Url;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class DeleteAccountController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(private Url $urlGenerator)
    {
    }

    /**
     * Undo delete account command.
     */
    public function undoDeleteAccount(Request $request, ORM $em): RedirectResponse
    {
        if ($this->getUser()) {
            $user = $this->getUser();
            $userDelete = $user->getUserDelete();

            // Sets userDelete to false.
            if (!empty($user->getUserDelete())) {
                $userDelete->isToBeDeleted(false);
            }

            // Get current time.
            $now = new \DateTime('now');

            // Set the undo date of deletion.
            $userDelete->setUndoDeletionDate($now);
            $user->setUserDelete($userDelete);

            // Verfiy csrf_token before flushing to table.
            if ($this->isCsrfTokenValid(
                'undo_delete'.$user->getId(),
                $request->request->get('_undo_delete_account_token')
            )) {
                if (!empty($user->getUserDelete())) {
                    $em->persist($userDelete);
                    $em->flush();
                }

                // Redirect to previous browsing page after being logged in.
                if ($request->get('_target_path')) {
                    return new RedirectResponse($request->get('_target_path'));
                } elseif (empty($request->get('_target_path'))) {
                    return new RedirectResponse($this->urlGenerator->generate('user_profile'));
                }
            }
        }
    }

    /**
     * Delete user account.
     *
     * Steps:
     * 1) Create userDelete entity and set $isuserDeleted to true.
     * 2) Persist to $user.
     */
    public function delete(Request $request, ORM $em): Response
    {
        if ($this->getUser()) {
            // Assign logged user to variable $user.
            $user = $this->getUser();

            $userDelete = !empty($user->getUserDelete()) ? $user->getUserDelete() : new UserDelete();

            // Get current time.
            $date = new \DateTime('now');

            // Add an interval of 2 days (48 hrs).
            // P2D => Period of 2 Days.
            $dateDeletion = $date->add(new \DateInterval('P2D'));

            // Sets toBeDeleted to true.
            $userDelete->settoBeDeleted(true)
                       ->setdateDeletion($dateDeletion);

            $user->setUserDelete($userDelete);

            // Verfiy csrf_token before flushing to table.
            if ($this->isCsrfTokenValid(
                'delete'.$user->getId(),
                $request->request->get('_delete_account_token')
            )) {
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('user_profile', [], Response::HTTP_SEE_OTHER);
            }
        }
    }
}
