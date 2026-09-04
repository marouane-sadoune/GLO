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
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logement_id')->constrained('logements')->restrictOnDelete();
            $table->foreignId('occupation_id')->nullable()->unique()->constrained('occupations')->nullOnDelete();
            $table->foreignId('occupant_id')->constrained('occupants')->restrictOnDelete();
            $table->date('vacation_date');
            $table->string('reason', 255)->nullable();
            $table->string('legal_basis', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('logement_id');
            $table->index('occupant_id');
            $table->index('vacation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};
