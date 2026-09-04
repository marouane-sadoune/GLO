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
        Schema::create('logements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments')->restrictOnDelete();
            $table->string('inventory_number', 60);
            $table->string('location_fr', 255);
            $table->string('location_ar', 255)->nullable();
            $table->string('housing_category'); // ADMINISTRATIVE | FUNCTIONAL
            $table->string('housing_status')->default('VACANT'); // VACANT | OCCUPIED
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['establishment_id', 'inventory_number']);
            $table->index('housing_status');
            $table->index('housing_category');
            $table->index(['establishment_id', 'housing_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logements');
    }
};
