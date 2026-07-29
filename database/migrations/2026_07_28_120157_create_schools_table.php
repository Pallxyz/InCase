<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['SMK', 'SMA', 'SMP'])->default('SMK');
            $table->unsignedTinyInteger('days_per_week')->default(5);
            $table->timestamps();
        });

        // Backfill: bikin 1 row buat setiap nama sekolah yang udah ada,
        // asumsi default SMK 6 hari (sesuai data seeder sekarang).
        $existingSchoolNames = DB::table('users')
            ->whereNotNull('school_name')
            ->pluck('school_name')
            ->merge(
                DB::table('school_classes')->whereNotNull('school_name')->pluck('school_name')
            )
            ->unique()
            ->filter();

        foreach ($existingSchoolNames as $name) {
            DB::table('schools')->insertOrIgnore([
                'name' => $name,
                'type' => 'SMK',
                'days_per_week' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
