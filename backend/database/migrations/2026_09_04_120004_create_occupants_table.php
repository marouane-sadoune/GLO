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
        Schema::create('occupants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->nullable()->constrained('establishments')->nullOnDelete();
            $table->string('first_name_fr', 100);
            $table->string('last_name_fr', 100);
            $table->string('first_name_ar', 100)->nullable();
            $table->string('last_name_ar', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('employee_number', 30)->nullable()->unique();
            $table->string('framework', 120)->nullable();
            $table->string('position', 120)->nullable();
            $table->string('status')->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['last_name_fr', 'first_name_fr']);
            $table->index('establishment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupants');
    }
};
