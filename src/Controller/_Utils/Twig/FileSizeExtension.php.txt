<?php

// src/Controller/_Utils/Twig/FileSizeExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Convert bytes into human readable format
 * Ex. 457733 => 4.57Mb.
 */
class FileSizeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('fileSize', [$this, 'humanReadableSize']),
        ];
    }

    public function humanReadableSize(string $file_path): string
    {
        $current_dir_path = getcwd();
        $bytes = filesize($current_dir_path.$file_path);

        if ($bytes < 1) {
            return 0;
        }

        $units = ['B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb'];
        $factor = min(floor(log($bytes, 1024)), 5);
        $value = round($bytes / (1024 ** $factor), $factor > 1 ? 2 : 0);

        return $value.$units[$factor];

        /* Option 2 Return the human readable size without the unit */
        // return $u ? $value . $units[$factor] : $value;

        // https://gist.github.com/liunian/9338301
    }
}
