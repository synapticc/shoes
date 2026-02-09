<?php

// src/Form/Events/AddressListener.php

namespace App\Form\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AddressListener.
 */
class AddressListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    /**
     * Add selected cities to <select>.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(PreSubmitEvent $event): void
    {
        $user = $event->getData();
        $form = $event->getForm();

        if (!$user) {
            return;
        }

        if (!empty($event->getData()['city'])) {
            $city = $event->getData()['city'];
            $form->add('city', ChoiceType::class, ['choices' => [$city => $city]]);
        }
    }
}
