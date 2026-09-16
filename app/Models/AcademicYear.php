<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_start',
        'year_end',
        'semester',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function getLabelAttribute(): string
    {
        $semesterLabel = $this->semester === 'ganjil' ? 'Ganjil' : 'Genap';
        return "{$this->year_start}/{$this->year_end} - {$semesterLabel}";
    }

    /**
     * Ambil tahun ajaran yang lagi aktif.
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
}