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
        Schema::table('orders', function (Blueprint $table) {
            // Order identification
            $table->string('order_number')->unique()->after('id');
            
            // Delivery information
            $table->enum('delivery_method', ['pickup', 'delivery', 'express'])->default('pickup')->after('total_amount');
            $table->text('delivery_address')->nullable()->after('delivery_method');
            $table->string('delivery_zone')->nullable()->after('delivery_address');
            $table->date('estimated_delivery_date')->nullable()->after('delivery_zone');
            
            // Customer notes
            $table->text('order_notes')->nullable()->after('estimated_delivery_date');
            
            // Cancellation information
            $table->timestamp('cancelled_at')->nullable()->after('order_notes');
            $table->string('cancelled_reason')->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'delivery_method',
                'delivery_address',
                'delivery_zone',
                'estimated_delivery_date',
                'order_notes',
                'cancelled_at',
                'cancelled_reason'
            ]);
        });
    }
};