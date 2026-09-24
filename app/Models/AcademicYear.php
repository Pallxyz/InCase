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
     *  SCOPE
     * ============================================ */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
}