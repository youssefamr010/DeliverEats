<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surge_pricing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('set null');
            $table->string('area')->nullable(); // geographic area identifier
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->string('strategy'); // flat, multiplier, time_based
            $table->string('reason'); // high_demand, bad_weather, peak_hours
            $table->json('factors')->nullable(); // detailed breakdown
            $table->timestamp('triggered_at');
            $table->timestamp('expired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricing_logs');
    }
};
