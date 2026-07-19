<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('subject_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('subject_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('item_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->timestamps();
    });
}
};
