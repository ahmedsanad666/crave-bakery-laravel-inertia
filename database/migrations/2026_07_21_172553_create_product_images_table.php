<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'thumbnail')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('thumbnail')->nullable()->after('is_active');
            });
        }

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');

        if (Schema::hasColumn('products', 'thumbnail')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('thumbnail');
            });
        }
    }
};
