<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('items', 'quantity')) {
            Schema::table('items', function (Blueprint $table) {
                $table->integer('quantity')->default(1)->after('rfid_uid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('items', 'quantity')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};