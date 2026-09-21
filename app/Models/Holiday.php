<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_name', 'class_id', 'date', 'name', 'created_by'])]
class Holiday extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cari libur yang berlaku buat sekolah + kelas pada tanggal tertentu.
     * class_id null di row artinya berlaku buat semua kelas di sekolah itu.
     * Return null kalau tidak libur.
     */
    public static function findFor(?string $schoolName, ?int $classId, \Carbon\Carbon $date): ?self
    {
        if (blank($schoolName)) {
            return null;
        }

        return static::where('school_name', $schoolName)
            ->whereDate('date', $date)
            ->where(function ($query) use ($classId) {
                $query->whereNull('class_id');

                if ($classId) {
                    $query->orWhere('class_id', $classId);
                }
            })
            ->first();
    }

    /**
     * Cek apakah tanggal tertentu itu libur buat sekolah + kelas tertentu.
     */
    public static function isHoliday(string $schoolName, ?int $classId, \Carbon\Carbon $date): bool
    {
        return static::findFor($schoolName, $classId, $date) !== null;
    }
}