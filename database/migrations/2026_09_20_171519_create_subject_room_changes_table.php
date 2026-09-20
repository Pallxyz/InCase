<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perubahan ruang belajar UNTUK SATU TANGGAL SAJA
 * (mis. hari ini pindah ke masjid). Jadwal utama tidak berubah,
 * jadi besoknya otomatis kembali ke ruang biasa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('date');
            $table->string('location');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Satu pelajaran hanya boleh punya satu perubahan ruang per tanggal.
            $table->unique(['subject_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_room_changes');
    }
};