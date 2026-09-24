<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /** Kategori barang yang boleh dijawab "dikumpulkan / terbawa teman" saat cek pulang. */
    public const SUBMITTABLE_CATEGORIES = ['Book'];

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
     * Barang seperti buku bisa saja "dikumpulkan ke guru" atau "kebawa teman",
     * jadi siswa boleh pilih itu selain "hilang". Barang pribadi (botol minum,
     * dompet, tepak makan, dll) yang tidak kembali cuma bisa dijelaskan "hilang",
     * karena tidak ada alasan wajar barang pribadi "dikumpulkan".
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->category, self::SUBMITTABLE_CATEGORIES, true);
    }

    /**
     * Owner of the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subjects requiring this item.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_items'
        );
    }

    /**
     * RFID scan history.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}