<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('product_variants', function (Blueprint $table) {
        $table->id();
        $table->string('cheese_product');
        $table->string('variant_name');
        $table->decimal('weight_grams', 10, 2);
        $table->timestamps();
    });

    Schema::table('orders', function (Blueprint $table) {
        $table->string('variant')->nullable()->after('cheese_product');
        $table->decimal('quantity_pieces', 10, 2)->nullable()->after('variant');
    });
}

public function down(): void
{
    Schema::dropIfExists('product_variants');
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['variant', 'quantity_pieces']);
    });
}
};
