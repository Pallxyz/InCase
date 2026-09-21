<?php

namespace Database\Seeders;

/**
 * Data jadwal demo: SMKN 1 Cirebon, jurusan RPL, 6 kelas.
 * Murni data (tanpa database) supaya bisa dites tanpa Laravel.
 *
 * Jadwal dijamin TIDAK BENTROK:
 *  - satu guru per mapel (8 mapel, 8 guru)
 *  - di jam yang sama, 6 kelas mendapat 6 mapel BERBEDA (jadi tidak ada guru mengajar dua kelas sekaligus)
 *  - tiap kelas punya ruangnya sendiri (tidak ada ruang dobel)
 */
final class RplTimetable
{
    /** [nama mapel, nama guru, barang wajib] */
    public const SUBJECTS = [
        ['Matematika', 'Andi Pratama', ['Buku Paket Matematika', 'Kalkulator']],
        ['Bahasa Indonesia', 'Sari Wulandari', ['Buku Tulis Bahasa Indonesia']],
        ['Bahasa Inggris', 'Rina Kusuma', ['Kamus Bahasa Inggris']],
        ['Pemrograman Web', 'Budi Santoso', ['Laptop', 'Charger Laptop', 'Flashdisk']],
        ['Basis Data', 'Dewi Lestari', ['Laptop', 'Charger Laptop']],
        ['Pemrograman Berorientasi Objek', 'Hendra Gunawan', ['Laptop', 'Charger Laptop', 'Mouse']],
        ['Pemrograman Mobile', 'Yusuf Maulana', ['Laptop', 'Charger Laptop', 'Kabel Data']],
        ['Produk Kreatif dan Kewirausahaan', 'Nur Aini', ['Buku Tulis PKK']],
    ];

    /** [nama kelas, tingkat, ruang] */
    public const CLASSES = [
        ['X RPL 1', 'X', 'R101'],
        ['X RPL 2', 'X', 'R102'],
        ['XI RPL 1', 'XI', 'R201'],
        ['XI RPL 2', 'XI', 'R202'],
        ['XII RPL 1', 'XII', 'R301'],
        ['XII RPL 2', 'XII', 'R302'],
    ];

    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public const SLOTS = [['07:00', '08:30'], ['08:45', '10:15'], ['10:30', '12:00']];

    public const ITEM_CATEGORY = [
        'Buku Paket Matematika' => 'Book',
        'Buku Tulis Bahasa Indonesia' => 'Book',
        'Kamus Bahasa Inggris' => 'Book',
        'Buku Tulis PKK' => 'Book',
        'Kalkulator' => 'Electronics',
        'Laptop' => 'Electronics',
        'Charger Laptop' => 'Electronics',
        'Flashdisk' => 'Electronics',
        'Mouse' => 'Electronics',
        'Kabel Data' => 'Electronics',
    ];

    /**
     * @return list<array{class:int, day:string, start:string, end:string, subject:int}>
     */
    public static function entries(): array
    {
        $subjectCount = count(self::SUBJECTS);
        $entries = [];

        foreach (self::CLASSES as $c => $class) {
            foreach (self::DAYS as $d => $day) {
                foreach (self::SLOTS as $s => [$start, $end]) {
                    $entries[] = [
                        'class' => $c,
                        'day' => $day,
                        'start' => $start,
                        'end' => $end,
                        // untuk (hari, jam) yang sama, tiap kelas dapat mapel berbeda
                        'subject' => ($c + 3 * $s + 2 * $d) % $subjectCount,
                    ];
                }
            }
        }

        return $entries;
    }

    /** Semua nama barang yang muncul sebagai barang wajib. */
    public static function allItemNames(): array
    {
        return array_keys(self::ITEM_CATEGORY);
    }
}