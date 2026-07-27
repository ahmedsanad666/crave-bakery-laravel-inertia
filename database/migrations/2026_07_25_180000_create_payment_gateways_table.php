<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            // Unique machine-readable identifier — used in code to resolve the gateway
            $table->string('name')->unique();

            // Human-readable label shown in admin and checkout
            $table->string('label');

            // Short description shown on checkout under the gateway name
            $table->text('description')->nullable();

            // Path to the gateway logo (stored in /storage/gateways/)
            $table->string('logo')->nullable();

            // Whether this gateway is available on the checkout page
            $table->boolean('is_enabled')->default(false);

            // Whether to use sandbox/test mode for this gateway
            $table->boolean('is_test_mode')->default(true);

            // All API credentials stored as encrypted JSON
            $table->text('config')->nullable();

            // Instructions shown to customer (used by Cash on Delivery)
            $table->text('instructions')->nullable();

            // Controls the order gateways appear in checkout
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
