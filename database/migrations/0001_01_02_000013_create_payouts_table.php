<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('order_total', 10, 2);
            $table->decimal('restaurant_amount', 10, 2);
            $table->decimal('rider_amount', 10, 2);
            $table->decimal('platform_amount', 10, 2);
            $table->decimal('platform_commission_pct', 5, 2);
            $table->string('status')->default('pending'); // pending, processed, paid
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
