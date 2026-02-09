<?php

// src/Controller/_Utils/Twig/VariableTypeExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 *  Get the type of a variable.
 */
class VariableTypeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('type', [$this, 'gettype']),
        ];
    }

    public function gettype($variable): string
    {
        return gettype($variable);
    }
}
