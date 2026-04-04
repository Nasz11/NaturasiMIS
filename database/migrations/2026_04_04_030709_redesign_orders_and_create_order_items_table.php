<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add client fields to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->string('client_name')->nullable()->after('client_id');
            $table->date('order_date')->nullable()->after('client_name');
        });

        // Create order_items table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('cheese_product');
            $table->string('variant_name');
            $table->decimal('weight_grams', 10, 2);
            $table->decimal('quantity_pieces', 10, 2);
            $table->decimal('total_kg', 10, 4);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'client_name', 'order_date']);
        });
    }
};