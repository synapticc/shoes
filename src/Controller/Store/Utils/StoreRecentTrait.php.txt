<?php

// src/Controller/Store/Utils/StoreRecentTrait.php

namespace App\Controller\Store\Utils;

use App\Repository\User\Settings\MaxItemsRepository as MaxItems;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Handle cart for Store Detail page.
 */
trait StoreRecentTrait
{
    protected RequestStack $requestStack;
    protected MaxItems $max;
    private array $recent;

    #[Required]
    public function setStoreRecentRepository(RequestStack $requestStack, MaxItems $max): void
    {
        $this->requestStack = $requestStack;
        $this->max = $max;
    }

    protected function recent(array $pd)
    {
        $session = $this->requestStack->getSession();
        $id = (int) $pd['id'];

        if (!$session->has('recent')) {
            $session->set('recent', [$id]);
            $this->recent = $session->get('recent');
        } elseif ($session->has('recent')) {
            $this->recent = $session->get('recent');
            if (!in_array($id, $this->recent)) {
                array_unshift($this->recent, $id);
                $session->set('recent', array_values($this->recent));
            } else {
                $keyRemove = array_search($id, $this->recent);
                unset($this->recent[$keyRemove]);
                array_unshift($this->recent, $id);
                $session->set('recent', array_values($this->recent));
            }
        }

        $maxRecentItems = $this->max->recent();

        return array_slice($this->recent, 1, $maxRecentItems);
    }
}
