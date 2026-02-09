<?php

// src/Controller/_Utils/Twig/ColorFilterExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ColorFilterExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('is_excluded_color', [$this, 'isExcludedColor']),
        ];
    }

    /**
     * Check if color name is in excluded list.
     */
    public function isExcludedColor(string $colorName, array $excludeList): bool
    {
        if (empty($excludeList)) {
            return false;
        }

        foreach ($excludeList as $exclude) {
            if (false !== strpos($colorName, $exclude)) {
                return true;
            }
        }

        return false;
    }
}
