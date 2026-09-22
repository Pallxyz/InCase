<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scan dibedakan:
 *  - phase    : 'packing' (persiapan/berangkat) atau 'return' (cek sebelum pulang)
 *  - for_date : scan ini untuk HARI SEKOLAH tanggal berapa
 *               (scan malam hari dihitung untuk hari sekolah berikutnya)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_logs', function (Blueprint $table) {
            $table->string('phase', 20)->default('packing')->after('status');
            $table->date('for_date')->nullable()->after('phase');
            $table->index(['user_id', 'for_date']);
        });

        // Scan lama: dianggap persiapan untuk tanggal scan itu sendiri.
        DB::table('scan_logs')->whereNull('for_date')->update(['for_date' => DB::raw('DATE(scanned_at)')]);
    }

    public function down(): void
    {
        Schema::table('scan_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'for_date']);
            $table->dropColumn(['phase', 'for_date']);
        });
    }
};