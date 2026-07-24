<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_status', [
                'pending',
                'payment_confirmed',
                'processing',
                'ready_for_pickup',
                'completed',
                'cancelled'
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_status', [
                'pending',
                'processing',
                'ready_for_pickup',
                'completed',
                'cancelled'
            ])->default('pending')->change();
        });
    }
};