<?php

// src/Controller/_Utils/Twig/ConvertToFullNameExtension.php

namespace App\Controller\_Utils\Twig;

use App\Controller\_Utils\Attributes;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Convert abbreviated name to their full name.
 */
class ConvertToFullNameExtension extends AbstractExtension
{
    use Attributes;

    public function getFilters(): array
    {
        return [
            new TwigFilter('name', [$this, 'convertName']),
        ];
    }

    public function convertName(string $txt): string
    {
        return $this->name($txt);
    }
}
