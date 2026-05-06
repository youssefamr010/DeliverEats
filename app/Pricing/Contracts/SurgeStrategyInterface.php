<?php

namespace App\Pricing\Contracts;

/**
 * Strategy Pattern interface for surge pricing calculation.
 */
interface SurgeStrategyInterface
{
    /**
     * Calculate the surge multiplier based on given factors.
     *
     * @param array $factors ['demand' => int, 'weather' => string, 'hour' => int, ...]
     * @return float Multiplier (1.0 = no surge, 2.0 = double price)
     */
    public function calculate(array $factors): float;

    /**
     * Get the strategy name
     */
    public function getName(): string;
}
