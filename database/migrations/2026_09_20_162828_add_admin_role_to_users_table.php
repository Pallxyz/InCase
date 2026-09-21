<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher', 'student'])
                ->default('student')
                ->change();
        });
    }

    public function down(): void
    {
        // Admin dijadikan guru dulu supaya nilai enum-nya masih valid.
        DB::table('users')->where('role', 'admin')->update(['role' => 'teacher']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['teacher', 'student'])
                ->default('student')
                ->change();
        });
    }
};