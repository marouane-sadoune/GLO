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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('password')->constrained('departments')->nullOnDelete();
            $table->foreignId('establishment_id')->nullable()->after('department_id')->constrained('establishments')->nullOnDelete();
            $table->boolean('active')->default(true)->after('establishment_id');
            $table->timestamp('last_login_at')->nullable()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['establishment_id']);
            $table->dropColumn(['department_id', 'establishment_id', 'active', 'last_login_at']);
        });
    }
};
