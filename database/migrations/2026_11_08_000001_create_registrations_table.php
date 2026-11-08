<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('telepon', 20);
            $table->string('token', 64)->unique();
            $table->enum('status', ['terdaftar', 'hadir', 'tidak_hadir'])->default('terdaftar');
            $table->dateTime('waktu_hadir')->nullable();
            $table->timestamps();

            $table->unique(['seminar_id', 'email']);
            $table->unique(['seminar_id', 'telepon']);
            $table->index('seminar_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
