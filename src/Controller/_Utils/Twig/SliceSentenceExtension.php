<?php

// src/Controller/_Utils/Twig/SliceSentenceExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Truncate a sentence to a given length of characters.
 */
class SliceSentenceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sentence', [$this, 'truncateStringToSentence']),
        ];
    }

    public function truncateStringToSentence($string, $limit)
    {
        $string = substr($string, 0, $limit);
        if (false !== ($breakpoint = strrpos($string, '.'))) {
            $string = substr($string, 0, $breakpoint).'.';
        }

        return $string;
    }
}
