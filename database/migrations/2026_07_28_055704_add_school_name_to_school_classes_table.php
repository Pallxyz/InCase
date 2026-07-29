<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('name');
        });

        // Backfill data lama (yang udah ada sebelum kolom ini) sebagai SMKN 1 Cirebon
        DB::table('school_classes')
            ->whereNull('school_name')
            ->update(['school_name' => 'SMKN 1 Cirebon']);
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('school_name');
        });
    }
};