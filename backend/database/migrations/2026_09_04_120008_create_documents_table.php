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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logement_id')->nullable()->constrained('logements')->cascadeOnDelete();
            $table->foreignId('occupant_id')->nullable()->constrained('occupants')->nullOnDelete();
            $table->foreignId('occupation_id')->nullable()->constrained('occupations')->nullOnDelete();
            $table->string('type'); // ASSIGNMENT_ORDER, COMMITTEE_MINUTES, etc.
            $table->string('document_number', 60)->nullable();
            $table->date('document_date')->nullable();
            $table->string('file_path', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedInteger('size_bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('logement_id');
            $table->index('occupant_id');
            $table->index('occupation_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
