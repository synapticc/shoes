<?php

// src/Controller/_Utils/Twig/CountryExtension.php

namespace App\Controller\_Utils\Twig;

use Symfony\Component\Intl\Countries;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Convert country code to their full name.
 */
class CountryExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('country', [$this, 'countryCode']),
        ];
    }

    public function countryCode(string $code): string
    {
        \Locale::setDefault('en');

        $country = Countries::getName($code);
        // 'GB' will display 'United Kingdom'

        return $country;
    }
}
