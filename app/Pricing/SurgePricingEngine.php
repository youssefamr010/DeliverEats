<?php

namespace App\Pricing;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Rider;
use App\Models\SurgePricingLog;
use App\Pricing\Contracts\SurgeStrategyInterface;
use App\Pricing\Strategies\FlatSurgeStrategy;
use App\Pricing\Strategies\MultiplierSurgeStrategy;
use App\Pricing\Strategies\TimeBasedSurgeStrategy;

/**
 * Surge Pricing Engine using Strategy Pattern.
 * Evaluates demand, weather, time of day, and rider availability.
 * Maximum cap: 3.0x. Auto-rollback when demand drops.
 */
class SurgePricingEngine
{
    private SurgeStrategyInterface $strategy;
    private float $maxMultiplier = 3.0;
    private float $minMultiplier = 1.0;

    public function __construct(?SurgeStrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new MultiplierSurgeStrategy();
    }

    /**
     * Set the pricing strategy
     */
    public function setStrategy(SurgeStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Get a strategy instance by name
     */
    public static function getStrategyByName(string $name): SurgeStrategyInterface
    {
        return match($name) {
            'flat' => new FlatSurgeStrategy(),
            'multiplier' => new MultiplierSurgeStrategy(),
            'time_based' => new TimeBasedSurgeStrategy(),
            default => new MultiplierSurgeStrategy(),
        };
    }

    /**
     * Calculate surge multiplier for a restaurant/area
     */
    public function calculateSurge(?Restaurant $restaurant = null, array $extraFactors = []): array
    {
        // Gather factors
        $factors = $this->gatherFactors($restaurant, $extraFactors);

        // Calculate raw multiplier using current strategy
        $rawMultiplier = $this->strategy->calculate($factors);

        // Apply cap
        $multiplier = min($rawMultiplier, $this->maxMultiplier);
        $multiplier = max($multiplier, $this->minMultiplier);
        $multiplier = round($multiplier, 2);

        // Determine reason
        $reason = $this->determineReason($factors, $multiplier);

        return [
            'multiplier' => $multiplier,
            'strategy'   => $this->strategy->getName(),
            'reason'     => $reason,
            'factors'    => $factors,
            'capped'     => $rawMultiplier > $this->maxMultiplier,
        ];
    }

    /**
     * Calculate and log the surge pricing
     */
    public function calculateAndLog(?Restaurant $restaurant = null, array $extraFactors = []): SurgePricingLog
    {
        $result = $this->calculateSurge($restaurant, $extraFactors);

        // Expire previous active surges for this restaurant
        if ($restaurant) {
            SurgePricingLog::where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'expired_at' => now()]);
        }

        return SurgePricingLog::create([
            'restaurant_id' => $restaurant?->id,
            'multiplier'    => $result['multiplier'],
            'strategy'      => $result['strategy'],
            'reason'        => $result['reason'],
            'factors'       => $result['factors'],
            'triggered_at'  => now(),
            'is_active'     => $result['multiplier'] > 1.0,
        ]);
    }

    /**
     * Gather all factors for surge calculation
     */
    private function gatherFactors(?Restaurant $restaurant, array $extraFactors): array
    {
        $factors = [
            'hour'             => (int) date('H'),
            'day_of_week'      => (int) date('w'),
            'weather'          => $extraFactors['weather'] ?? 'clear',
            'demand'           => $extraFactors['demand'] ?? $this->getCurrentDemand($restaurant),
            'available_riders' => $extraFactors['available_riders'] ?? $this->getAvailableRiders(),
        ];

        return array_merge($factors, $extraFactors);
    }

    /**
     * Get current demand (orders in last hour)
     */
    private function getCurrentDemand(?Restaurant $restaurant): int
    {
        $query = Order::where('created_at', '>=', now()->subHour());

        if ($restaurant) {
            $query->where('restaurant_id', $restaurant->id);
        }

        return $query->count();
    }

    /**
     * Get number of available riders
     */
    private function getAvailableRiders(): int
    {
        return Rider::where('is_online', true)->where('is_available', true)->count();
    }

    /**
     * Determine human-readable reason for surge
     */
    private function determineReason(array $factors, float $multiplier): string
    {
        if ($multiplier <= 1.0) return 'normal';

        $reasons = [];
        if (($factors['demand'] ?? 0) > 10) $reasons[] = 'high_demand';
        if (in_array($factors['weather'] ?? 'clear', ['rain', 'storm', 'snow', 'extreme'])) $reasons[] = 'bad_weather';
        $hour = $factors['hour'] ?? 12;
        if (($hour >= 12 && $hour <= 14) || ($hour >= 18 && $hour <= 21)) $reasons[] = 'peak_hours';
        if (($factors['available_riders'] ?? 10) < 5) $reasons[] = 'low_rider_availability';

        return implode(', ', $reasons) ?: 'demand_increase';
    }
}
