<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'novvalino@incase.test')->first();

        $items = [

            /*
            |--------------------------------------------------------------------------
            | Perangkat
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Laptop',
                'category' => 'Electronics',
            ],

            [
                'name' => 'Charger Laptop',
                'category' => 'Electronics',
            ],

            [
                'name' => 'Mouse',
                'category' => 'Electronics',
            ],

            [
                'name' => 'Flashdisk',
                'category' => 'Electronics',
            ],

            /*
            |--------------------------------------------------------------------------
            | Buku Tulis
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Buku Tulis Matematika',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis Bahasa Indonesia',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis Bahasa Inggris',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis Sejarah Indonesia',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis PAI',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis PPKN',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis PKK',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis MKK',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis MPP',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Tulis BK',
                'category' => 'Book',
            ],

            /*
            |--------------------------------------------------------------------------
            | Buku Paket
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Buku Paket Matematika',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Paket Bahasa Indonesia',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Paket Bahasa Inggris',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Paket Sejarah Indonesia',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Paket PAI',
                'category' => 'Book',
            ],

            [
                'name' => 'Buku Paket PPKN',
                'category' => 'Book',
            ],

            /*
            |--------------------------------------------------------------------------
            | ATK
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Pulpen',
                'category' => 'Stationery',
            ],

            [
                'name' => 'Pensil',
                'category' => 'Stationery',
            ],

            [
                'name' => 'Penghapus',
                'category' => 'Stationery',
            ],

            [
                'name' => 'Penggaris',
                'category' => 'Stationery',
            ],

            /*
            |--------------------------------------------------------------------------
            | Situasional
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Baju Olahraga',
                'category' => 'Sports',
            ],

            [
                'name' => 'Sepatu Olahraga',
                'category' => 'Sports',
            ],

            [
                'name' => 'Raket Badminton',
                'category' => 'Sports',
            ],

            [
                'name' => 'Bet Tenis Meja',
                'category' => 'Sports',
            ],

            [
                'name' => 'Jangka',
                'category' => 'Stationery',
            ],

            [
                'name' => 'Kalkulator',
                'category' => 'Electronics',
            ],

        ];

        $i = 1;

        foreach ($items as $item) {

            Item::create([

                'user_id' => $student->id,

                'name' => $item['name'],

                'category' => $item['category'],

                'rfid_uid' => 'RFID' . str_pad($i, 4, '0', STR_PAD_LEFT),

                'description' => null,

                'status' => 'active',

            ]);

            $i++;
        }
    }
}