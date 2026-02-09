<?php

// src/Controller/_Utils/Twig/DateMontheExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Attribute\AsTwigFilter;

/**
 * Extracts the browsers from the UserAgent string.
 */
class DateMontheExtension
{
    #[AsTwigFilter('dateMonth')]
    public function dateMonth($date)
    {
        return $date->format('j M Y');
    }
}
