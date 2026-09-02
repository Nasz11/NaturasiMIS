<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('quantity_needed', 10, 8)->change();
        });
    }
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('quantity_needed', 10, 2)->change();
        });
    }
};