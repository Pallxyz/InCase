<?php

namespace App\Http\Requests\Concerns;

use App\Services\ScheduleConflictChecker;
use Illuminate\Validation\Validator;

trait ChecksScheduleConflicts
{
    /**
     * Dipanggil dari withValidator() di request jadwal.
     * Dijalankan SETELAH validasi dasar lolos (jam valid, kelas ada, dst).
     */
    protected function checkScheduleConflicts(
        Validator $validator,
        ?int $academicYearId,
        ?int $teacherId,
        ?int $ignoreSubjectId = null,
    ): void {
        $validator->after(function (Validator $validator) use ($academicYearId, $teacherId, $ignoreSubjectId) {
            // Kalau form sudah salah (mis. jam selesai < jam mulai), tidak usah dicek bentrok.
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // Jadwal yang sengaja dinonaktifkan tidak perlu dicek.
            if ($this->has('is_active') && ! $this->boolean('is_active')) {
                return;
            }

            $conflicts = app(ScheduleConflictChecker::class)->check(
                $this->only(['class_id', 'day', 'start_time', 'end_time', 'location']),
                $academicYearId,
                $teacherId,
                $ignoreSubjectId,
            );

            foreach ($conflicts as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}