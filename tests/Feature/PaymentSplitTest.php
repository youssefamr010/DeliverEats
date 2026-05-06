<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PaymentService;
use Tests\TestCase;

class PaymentSplitTest extends TestCase
{
    /** @test */
    public function splits_correctly_with_15_percent_commission(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 25.00, subtotal: 20.00, deliveryFee: 3.00,
            surgeFee: 0, tip: 2.00, commissionRate: 15.00
        );

        $this->assertEquals(17.00, $result['restaurant_amount']); // 20 - 3 (15%)
        $this->assertEquals(5.00, $result['rider_amount']); // 3 + 2
        $this->assertEquals(3.00, $result['platform_amount']); // 3 commission + 0 surge
    }

    /** @test */
    public function splits_correctly_with_20_percent_commission(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 30.00, subtotal: 25.00, deliveryFee: 3.00,
            surgeFee: 0, tip: 2.00, commissionRate: 20.00
        );

        $this->assertEquals(20.00, $result['restaurant_amount']); // 25 - 5 (20%)
        $this->assertEquals(5.00, $result['rider_amount']); // 3 + 2
        $this->assertEquals(5.00, $result['platform_amount']); // 5 commission
    }

    /** @test */
    public function surge_fee_goes_to_platform(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 28.00, subtotal: 20.00, deliveryFee: 3.00,
            surgeFee: 3.00, tip: 2.00, commissionRate: 15.00
        );

        // Platform gets: commission (3) + surge (3) = 6
        $this->assertEquals(6.00, $result['platform_amount']);
    }

    /** @test */
    public function tip_goes_entirely_to_rider(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 30.00, subtotal: 20.00, deliveryFee: 3.00,
            surgeFee: 0, tip: 7.00, commissionRate: 15.00
        );

        $this->assertEquals(10.00, $result['rider_amount']); // 3 + 7
    }

    /** @test */
    public function total_allocation_equals_order_total(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 35.50, subtotal: 25.00, deliveryFee: 4.00,
            surgeFee: 2.50, tip: 4.00, commissionRate: 12.00
        );

        $total = $result['restaurant_amount'] + $result['rider_amount'] + $result['platform_amount'];
        $this->assertEquals(35.50, $total);
    }

    /** @test */
    public function zero_commission_gives_restaurant_full_subtotal(): void
    {
        $service = app(PaymentService::class);
        $result = $service->calculateSplits(
            orderTotal: 25.00, subtotal: 20.00, deliveryFee: 3.00,
            surgeFee: 0, tip: 2.00, commissionRate: 0
        );

        $this->assertEquals(20.00, $result['restaurant_amount']);
    }
}
