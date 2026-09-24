<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'teacher_id',
    'class_id',
    'academic_year_id', // tambahin ini
    'name',
    'location',
    'homework',
    'has_exam',
    'day',
    'start_time',
    'end_time',
    'is_active',
])]
class Subject extends Model
{
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id'
        );
    }

    public function requiredItems()
    {
        return $this->hasMany(SubjectRequiredItem::class);
    }

    /**
     * Hanya jadwal milik tahun ajaran yang sedang AKTIF.
     * Kalau belum ada tahun ajaran aktif -> tidak ada jadwal yang tampil.
     * (Sengaja tidak di-cache: listener MQTT jalan terus, jadi harus baca
     * tahun ajaran aktif terbaru setiap kali.)
     */
    public function scopeInActiveYear(Builder $query): Builder
    {
        $year = AcademicYear::active()->first();   // ← tambahkan ->first()

        return $year
            ? $query->where($this->getTable() . '.academic_year_id', $year->id)
            : $query->whereRaw('1 = 0');
    }

    public function roomChanges()
    {
        return $this->hasMany(SubjectRoomChange::class);
    }

    /**
     * Ganti `location` tiap jadwal dengan ruang pengganti pada tanggal itu (kalau ada).
     * Hanya mengubah objek di memori untuk ditampilkan -- JANGAN di-save().
     * Ruang aslinya disimpan di atribut `original_location`.
     *
     * @param  iterable<Subject>  $subjects
     */
    public static function applyRoomChanges(iterable $subjects, \Carbon\Carbon $date): void
    {
        $subjects = collect($subjects);

        if ($subjects->isEmpty()) {
            return;
        }

        $changes = SubjectRoomChange::whereIn('subject_id', $subjects->pluck('id'))
            ->whereDate('date', $date)
            ->pluck('location', 'subject_id');

        foreach ($subjects as $subject) {
            if ($changes->has($subject->id)) {
                $subject->setAttribute('original_location', $subject->location);
                $subject->location = $changes[$subject->id];
            }
        }
    }

    protected function casts(): array
    {
        return [
            'has_exam' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
