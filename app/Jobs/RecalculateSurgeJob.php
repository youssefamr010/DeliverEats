<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Pricing\SurgePricingEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateSurgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Restaurant $restaurant;

    /**
     * Create a new job instance.
     */
    public function __construct(?Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $engine = new SurgePricingEngine();
        $engine->calculateAndLog($this->restaurant);
    }
}
