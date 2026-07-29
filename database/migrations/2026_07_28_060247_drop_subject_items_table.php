<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('subject_items');
    }

    public function down(): void
    {
        // Sengaja kosong — struktur lama ini emang salah desain, gak perlu direstore.
    }
};