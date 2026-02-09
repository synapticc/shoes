<?php

// src/Controller/_Utils/Twig/SliceWordExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Slice a sentence into separate words.
 */
class SliceWordExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('word', [$this, 'sliceWord']),
        ];
    }

    // public function sliceWord(string $string,  int $start, int $end): string
    // {
    //   $sentence = substr($string, $start, strpos(wordwrap($string, $end), "\n"));
    //   return $sentence;
    // }

    public function sliceWord(string $words, int $limit): string
    {
        // $append = ' &hellip;'
        // Add 1 to the specified limit becuase arrays start at 0
        $limit = $limit + 1;
        // Store each individual word as an array element
        // Up to the limit
        $words = explode(' ', $words, $limit);
        // Shorten the array by 1 because that final element will be the sum of all the words after the limit
        array_pop($words);
        // Implode the array for output, and append an ellipse
        $words = implode(' ', $words);

        // Return the result
        return $words;
    }
}
