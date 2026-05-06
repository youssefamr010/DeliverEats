<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('stripe_connect_id')->nullable()->after('owner_id');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->string('stripe_connect_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('stripe_connect_id');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('stripe_connect_id');
        });
    }
};
