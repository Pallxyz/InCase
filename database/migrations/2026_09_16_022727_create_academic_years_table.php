<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year_start'); // 2024
            $table->unsignedSmallInteger('year_end');   // 2025
            $table->enum('semester', ['ganjil', 'genap']);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['year_start', 'year_end', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};