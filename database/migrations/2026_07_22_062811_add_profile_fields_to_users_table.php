<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('student_id')->unique()->nullable();
            $table->string('school_name')->nullable()->after('student_id');
            $table->string('avatar')->nullable()->after('school_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'student_id',
                'school_name',
                'avatar',
            ]);
        });
    }
};
