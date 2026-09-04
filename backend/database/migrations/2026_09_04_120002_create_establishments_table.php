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
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('name_fr', 200);
            $table->string('name_ar', 200)->nullable();
            $table->string('type', 60)->nullable();
            $table->string('address', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['department_id', 'code']);
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
