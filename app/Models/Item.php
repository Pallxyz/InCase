<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /** Kategori barang yang boleh dijawab "dikumpulkan / terbawa teman" saat cek pulang. */
    public const SUBMITTABLE_CATEGORIES = ['Book'];

    /** Kategori yang valid, sama persis dengan pilihan di dropdown halaman Barang. */
    public const CATEGORIES = ['Book', 'Electronics', 'Sports'];

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'rfid_uid',
        'quantity',
        'description',
        'status',
    ];

    /**
     * Tebak kategori dari nama barang wajib jadwal. Ini AMAN dipakai cuma untuk
     * nama yang berasal dari SubjectSeeder/SubjectRequiredItem, karena kosakatanya
     * terkontrol: "Buku Paket ...", "Buku Tulis ...", "Laptop", "Charger Laptop".
     * Barang yang diketik bebas lewat opsi "Lainnya" di form kategorinya dipilih
     * manual sama siswa, jadi tidak lewat fungsi ini.
     *
     * Kalau nanti ada nama barang wajib baru yang polanya beda (tidak diawali
     * "Buku", "Laptop", atau "Charger"), fungsi ini bakal salah menebak jadi
     * Sports. Cek lagi kalau muncul barang aneh di kategori Olahraga.
     */
    public static function categoryFor(string $name): string
    {
        $lower = mb_strtolower(trim($name));

        if (str_starts_with($lower, 'buku')) {
            return 'Book';
        }

        if (str_starts_with($lower, 'laptop') || str_starts_with($lower, 'charger')) {
            return 'Electronics';
        }

        return 'Sports';
    }

    /**
     * Barang seperti buku bisa saja "dikumpulkan ke guru" atau "kebawa teman",
     * jadi siswa boleh pilih itu selain "hilang". Barang pribadi (botol minum,
     * dompet, tepak makan, dll) yang tidak kembali cuma bisa dijelaskan "hilang",
     * karena tidak ada alasan wajar barang pribadi "dikumpulkan".
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->category, self::SUBMITTABLE_CATEGORIES, true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}