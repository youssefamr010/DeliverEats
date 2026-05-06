<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event-sourcing inspired: every state change logged
        Schema::create('order_state_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('from_state')->nullable();
            $table->string('to_state');
            $table->string('actor_type'); // customer, restaurant, rider, system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('metadata')->nullable(); // reason, notes, extra data
            $table->timestamp('transitioned_at');
            $table->timestamps();

            $table->index(['order_id', 'transitioned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_state_logs');
    }
};
