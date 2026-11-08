<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('pemapar', 150);
            $table->text('deskripsi');
            $table->string('flyer');
            $table->string('lokasi', 150);
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->unsignedInteger('kuota');
            $table->enum('status', ['dibuka', 'ditutup'])->default('dibuka');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};
