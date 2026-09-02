<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->string('product_type');
            $table->decimal('quantity', 10, 2);
            $table->date('production_date');
            $table->enum('status', ['In Production', 'Curing', 'Completed'])->default('In Production');
            $table->text('remarks')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('cheese_type');
            $table->decimal('quantity', 10, 2);
            $table->date('start_date');
            $table->date('completion_date');
            $table->enum('status', ['In Production', 'Curing', 'Ready for Packaging', 'Completed'])->default('In Production');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('batches');
    }
};
