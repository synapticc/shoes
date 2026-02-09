<?php

// src/Controller/_Utils/Twig/DurationExtension.php

namespace App\Controller\_Utils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Calculate the difference (in seconds) between two datetime values.
 */
class DurationExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('visit', [$this, 'datetimeDiff']),
        ];
    }

    public function datetimeDiff($end_date, $start_date)
    {
        if ($end_date < $start_date) {
            return 0;
        }

        // Convert the dates into Unix timestamps
        $start_timestamp = $start_date->getTimestamp();
        $end_timestamp = $end_date->getTimestamp();

        // Calculate the difference in seconds
        $difference_in_seconds = abs($end_timestamp - $start_timestamp);

        // Convert the difference from seconds to minutes
        $difference_in_minutes = $difference_in_seconds / 60;

        return $difference_in_minutes;
    }
}
