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
        Schema::table('assignment_requests', function (Blueprint $table) {
            $table->date('verified_at')->nullable()->after('submitted_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn('verified_at');
        });
    }
};
