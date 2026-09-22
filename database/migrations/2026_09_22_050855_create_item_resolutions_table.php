<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barang yang dibawa pagi tapi tidak kembali saat cek pulang:
 * siswa memilih "dikumpulkan" (ke guru) atau "hilang".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20); // 'submitted' | 'lost'
            $table->timestamps();

            $table->unique(['user_id', 'item_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_resolutions');
    }
};