<?php

namespace Tests\Feature;

use App\Pricing\SurgePricingEngine;
use App\Pricing\Strategies\FlatSurgeStrategy;
use App\Pricing\Strategies\MultiplierSurgeStrategy;
use App\Pricing\Strategies\TimeBasedSurgeStrategy;
use Tests\TestCase;

class SurgePricingTest extends TestCase
{
    /** @test */
    public function flat_strategy_returns_surge_when_demand_exceeds_threshold(): void
    {
        $strategy = new FlatSurgeStrategy(0.5, 10);
        $multiplier = $strategy->calculate(['demand' => 15]);
        $this->assertEquals(1.5, $multiplier);
    }

    /** @test */
    public function flat_strategy_returns_normal_when_demand_below_threshold(): void
    {
        $strategy = new FlatSurgeStrategy(0.5, 10);
        $multiplier = $strategy->calculate(['demand' => 5]);
        $this->assertEquals(1.0, $multiplier);
    }

    /** @test */
    public function multiplier_strategy_increases_with_demand(): void
    {
        $strategy = new MultiplierSurgeStrategy();
        $low = $strategy->calculate(['demand' => 3, 'available_riders' => 10, 'weather' => 'clear']);
        $high = $strategy->calculate(['demand' => 25, 'available_riders' => 10, 'weather' => 'clear']);
        $this->assertGreaterThan($low, $high);
    }

    /** @test */
    public function multiplier_strategy_increases_with_bad_weather(): void
    {
        $strategy = new MultiplierSurgeStrategy();
        $clear = $strategy->calculate(['demand' => 5, 'available_riders' => 10, 'weather' => 'clear']);
        $storm = $strategy->calculate(['demand' => 5, 'available_riders' => 10, 'weather' => 'storm']);
        $this->assertGreaterThan($clear, $storm);
    }

    /** @test */
    public function multiplier_strategy_increases_with_low_rider_availability(): void
    {
        $strategy = new MultiplierSurgeStrategy();
        $manyRiders = $strategy->calculate(['demand' => 5, 'available_riders' => 20, 'weather' => 'clear']);
        $fewRiders = $strategy->calculate(['demand' => 5, 'available_riders' => 1, 'weather' => 'clear']);
        $this->assertGreaterThan($manyRiders, $fewRiders);
    }

    /** @test */
    public function time_based_strategy_peaks_at_dinner(): void
    {
        $strategy = new TimeBasedSurgeStrategy();
        $morning = $strategy->calculate(['hour' => 10, 'demand' => 5]);
        $dinner = $strategy->calculate(['hour' => 19, 'demand' => 5]);
        $this->assertGreaterThan($morning, $dinner);
    }

    /** @test */
    public function engine_caps_multiplier_at_3x(): void
    {
        $engine = new SurgePricingEngine(new MultiplierSurgeStrategy());
        $result = $engine->calculateSurge(null, [
            'demand' => 100, 'available_riders' => 0, 'weather' => 'extreme'
        ]);
        $this->assertLessThanOrEqual(3.0, $result['multiplier']);
    }

    /** @test */
    public function engine_caps_report_when_raw_exceeds_max(): void
    {
        $engine = new SurgePricingEngine(new MultiplierSurgeStrategy());
        $result = $engine->calculateSurge(null, [
            'demand' => 100, 'available_riders' => 0, 'weather' => 'extreme'
        ]);
        $this->assertTrue($result['capped']);
    }

    /** @test */
    public function engine_rollback_when_demand_drops(): void
    {
        $engine = new SurgePricingEngine(new MultiplierSurgeStrategy());
        $result = $engine->calculateSurge(null, [
            'demand' => 0, 'available_riders' => 20, 'weather' => 'clear'
        ]);
        $this->assertEquals(1.0, $result['multiplier']);
    }

    /** @test */
    public function engine_can_switch_strategies(): void
    {
        $engine = new SurgePricingEngine();
        $engine->setStrategy(new FlatSurgeStrategy());
        $this->assertInstanceOf(SurgePricingEngine::class, $engine);
    }
}
