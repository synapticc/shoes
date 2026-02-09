<?php

// src/Controller/_Utils/Twig/BrowserExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use WhichBrowser\Parser;

/**
 * Extracts the browsers from the UserAgent string.
 */
class BrowserExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('browser', [$this, 'userAgent']),
        ];
    }

    public function userAgent(string $code): string
    {
        $browser = new Parser($code);
        $browser = $browser->toString();

        return $browser;
    }
}
