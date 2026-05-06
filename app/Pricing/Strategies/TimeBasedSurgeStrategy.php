<?php

namespace App\Pricing\Strategies;

use App\Pricing\Contracts\SurgeStrategyInterface;

/**
 * Time-based surge: peaks during lunch and dinner rush hours.
 */
class TimeBasedSurgeStrategy implements SurgeStrategyInterface
{
    public function calculate(array $factors): float
    {
        $hour = $factors['hour'] ?? (int) date('H');
        $demand = $factors['demand'] ?? 0;

        $multiplier = 1.0;

        // Lunch rush: 12 PM - 2 PM
        if ($hour >= 12 && $hour <= 14) {
            $multiplier += 0.3;
        }

        // Dinner rush: 6 PM - 9 PM
        if ($hour >= 18 && $hour <= 21) {
            $multiplier += 0.4;
        }

        // Late night premium: 10 PM - 12 AM
        if ($hour >= 22 || $hour <= 0) {
            $multiplier += 0.5;
        }

        // Early morning: 1 AM - 6 AM (low availability)
        if ($hour >= 1 && $hour <= 6) {
            $multiplier += 0.6;
        }

        // Demand boost on top of time
        if ($demand > 15) {
            $multiplier += 0.2;
        }

        return $multiplier;
    }

    public function getName(): string
    {
        return 'time_based';
    }
}
