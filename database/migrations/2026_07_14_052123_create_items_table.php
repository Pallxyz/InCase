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
        Schema::create('items', function (Blueprint $table) {

            $table->id();

            // Pemilik barang
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Nama barang
            $table->string('name');

            // Kategori barang
            $table->enum('category', [
                'Book',
                'Stationery',
                'Electronics',
                'Sports',
                'Personal',
                'Others'
            ]);

            // UID RFID
            $table->string('rfid_uid')->unique();

            // Deskripsi (opsional)
            $table->text('description')->nullable();

            // Barang masih dipakai atau tidak
            $table->enum('status', [
                'active',
                'archived'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};