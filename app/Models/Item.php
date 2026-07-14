<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'rfid',
        'compartment',
        'description',
        'status',
        'last_scan_at',
    ];

    protected $casts = [
        'last_scan_at' => 'datetime',
    ];

    // Ikut disertakan otomatis saat model ini di-JSON-kan (dipakai response AJAX)
    protected $appends = ['icon', 'signal_label', 'status_label', 'last_scan_text'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return match ($this->category) {
            'Elektronik' => 'device-phone-mobile',
            'Buku Pelajaran' => 'book-open',
            'Alat Tulis' => 'calculator',
            'Perlengkapan' => 'beaker',
            'Perlengkapan Olahraga' => 'tag',
            default => 'archive-box',
        };
    }

    public function getSignalLabelAttribute(): string
    {
        return match ($this->status) {
            'connected' => 'Kuat',
            'not_scanned' => 'Lemah',
            default => 'Tidak Ada',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'connected' => 'Terhubung',
            'not_scanned' => 'Belum Dipindai',
            default => 'Tanpa RFID',
        };
    }

    public function getLastScanTextAttribute(): string
    {
        return $this->last_scan_at ? $this->last_scan_at->diffForHumans() : 'Belum pernah dipindai';
    }
}