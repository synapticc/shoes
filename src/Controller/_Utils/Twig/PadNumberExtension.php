<?php

// src/Controller/_Utils/Twig/PadNumberExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Prefix a number with '0' if shorter than  a given length.
 */
class PadNumberExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('pad', [$this, 'padNumberPrefix']),
        ];
    }

    public function padNumberPrefix(int $number, int $pad_length): string
    {
        return sprintf('%0'.$pad_length.'d', $number);
    }
}
