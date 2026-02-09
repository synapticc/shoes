<?php

// src/Controller/Admin/RedirectController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends AbstractController
{
    /**
     * Redirect user to the Gitlab repository.
     */
    public function docs(): Response
    {
        return $this->redirect('https://gitlab.com/SynapticC/ecommerce/-/blob/main/README.md');
    }
}
