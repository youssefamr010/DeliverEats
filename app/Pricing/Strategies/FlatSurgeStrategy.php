<?php

namespace App\Pricing\Strategies;

use App\Pricing\Contracts\SurgeStrategyInterface;

/**
 * Flat surge: adds a fixed multiplier when demand exceeds threshold.
 */
class FlatSurgeStrategy implements SurgeStrategyInterface
{
    private float $surgeAmount;
    private int $demandThreshold;

    public function __construct(float $surgeAmount = 0.5, int $demandThreshold = 10)
    {
        $this->surgeAmount = $surgeAmount;
        $this->demandThreshold = $demandThreshold;
    }

    public function calculate(array $factors): float
    {
        $demand = $factors['demand'] ?? 0;

        if ($demand >= $this->demandThreshold) {
            return 1.0 + $this->surgeAmount;
        }

        return 1.0;
    }

    public function getName(): string
    {
        return 'flat';
    }
}
