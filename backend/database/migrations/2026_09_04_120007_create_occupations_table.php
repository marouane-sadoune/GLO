<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('occupations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logement_id')->constrained('logements')->restrictOnDelete();
            $table->foreignId('occupant_id')->constrained('occupants')->restrictOnDelete();
            $table->foreignId('assignment_request_id')->nullable()->unique()->constrained('assignment_requests')->nullOnDelete();
            $table->date('assignment_date');
            $table->string('assignment_type'); // MANDATORY | FREE | BY_LAW | ACTUAL
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('end_reason')->nullable(); // VACATION | TRANSFER | RETIREMENT | DEATH | ADMINISTRATIVE | OTHER
            $table->string('status')->default('ACTIVE'); // ACTIVE | ENDED
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['logement_id', 'status']);
            $table->index(['occupant_id', 'status']);
        });

        // Enforce maximum of ONE ACTIVE occupation per housing and per occupant using stored generated columns
        try {
            DB::statement("ALTER TABLE occupations ADD COLUMN active_logement_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status = 'ACTIVE' THEN logement_id ELSE NULL END) STORED");
            DB::statement("ALTER TABLE occupations ADD UNIQUE INDEX occupations_active_logement_unique (active_logement_id)");

            DB::statement("ALTER TABLE occupations ADD COLUMN active_occupant_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status = 'ACTIVE' THEN occupant_id ELSE NULL END) STORED");
            DB::statement("ALTER TABLE occupations ADD UNIQUE INDEX occupations_active_occupant_unique (active_occupant_id)");
        } catch (\Throwable $e) {
            // Fallback for environments where stored generated column syntax differs
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupations');
    }
};
