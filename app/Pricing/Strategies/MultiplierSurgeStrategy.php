<?php

namespace App\Pricing\Strategies;

use App\Pricing\Contracts\SurgeStrategyInterface;

/**
 * Multiplier surge: scales with demand, weather, and rider availability.
 */
class MultiplierSurgeStrategy implements SurgeStrategyInterface
{
    public function calculate(array $factors): float
    {
        $demand = $factors['demand'] ?? 0;
        $availableRiders = $factors['available_riders'] ?? 10;
        $weather = $factors['weather'] ?? 'clear';

        $multiplier = 1.0;

        // Demand-based scaling: more orders = higher multiplier
        if ($demand > 5)  $multiplier += 0.2;
        if ($demand > 10) $multiplier += 0.3;
        if ($demand > 20) $multiplier += 0.5;
        if ($demand > 35) $multiplier += 0.5;

        // Rider scarcity: fewer riders = higher multiplier
        if ($availableRiders < 5)  $multiplier += 0.3;
        if ($availableRiders < 3)  $multiplier += 0.4;
        if ($availableRiders < 1)  $multiplier += 0.5;

        // Weather impact
        $weatherMultipliers = [
            'clear'   => 0,
            'cloudy'  => 0,
            'rain'    => 0.3,
            'storm'   => 0.5,
            'snow'    => 0.6,
            'extreme' => 0.8,
        ];
        $multiplier += $weatherMultipliers[$weather] ?? 0;

        return $multiplier;
    }

    public function getName(): string
    {
        return 'multiplier';
    }
}
