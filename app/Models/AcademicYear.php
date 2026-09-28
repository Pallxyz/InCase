<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_start',
        'year_end',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ============================================
     *  RELASI
     * ============================================ */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /* ============================================
     *  TAHUN AJARAN AKTIF
     * ============================================ */

    /**
     * Ambil tahun ajaran yang lagi aktif (model, atau null kalau belum ada).
     * Pemakaian: AcademicYear::active()?->id
     *
     * Kalau butuh query builder-nya (mis. untuk ->exists()), pakai
     * AcademicYear::query()->where('is_active', true).
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Aktifkan satu tahun ajaran, matikan yang lain.
     */
    public function activate(): void
    {
        static::where('is_active', true)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /* ============================================
     *  ACCESSOR
     * ============================================ */

    /**
     * Nama tahun ajaran, contoh: "2025/2026"
     * Diakses via: $academicYear->name
     */
    public function getNameAttribute(): string
    {
        if ($this->year_start && $this->year_end) {
            return $this->year_start . '/' . $this->year_end;
        }

        return '-';
    }

    /**
     * Label semester dengan huruf kapital, contoh: "Ganjil"
     * Diakses via: $academicYear->semester_label
     */
    public function getSemesterLabelAttribute(): string
    {
        return ucfirst($this->semester ?? '-');
    }

    /**
     * Label lengkap, contoh: "2025/2026 - Ganjil"
     * Diakses via: $academicYear->label
     */
    public function getLabelAttribute(): string
    {
        $semesterLabel = $this->semester === 'ganjil' ? 'Ganjil' : 'Genap';

        return "{$this->year_start}/{$this->year_end} - {$semesterLabel}";
    }
}