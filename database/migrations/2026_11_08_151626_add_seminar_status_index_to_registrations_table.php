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
        Schema::table('registrations', function (Blueprint $table): void {
            $table->index(['seminar_id', 'status'], 'registrations_seminar_status_index');
            $table->index(['created_at', 'id'], 'registrations_recent_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex('registrations_seminar_status_index');
            $table->dropIndex('registrations_recent_index');
        });
    }
};
