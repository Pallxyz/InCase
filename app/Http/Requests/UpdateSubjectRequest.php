<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksScheduleConflicts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class UpdateSubjectRequest extends FormRequest
{
    use ChecksScheduleConflicts;

    public function authorize(): bool
    {
        return Auth::check()
            && in_array(Auth::user()->role, ['teacher', 'admin'], true);
    }

    protected function isAdmin(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** Checkbox yang tidak dicentang tidak dikirim browser -> anggap false (khusus admin). */
    protected function prepareForValidation(): void
    {
        if ($this->isAdmin()) {
            $this->merge(['has_exam' => $this->boolean('has_exam')]);
        }
    }

    public function rules(): array
    {
        // Field yang boleh diubah admin DAN guru
        $rules = [
            'class_id'       => ['required', 'exists:school_classes,id'],
            'homework'       => ['nullable', 'string', 'max:1000'],
            'required_items' => ['nullable', 'string'],
        ];

        // Field yang cuma boleh diubah admin
        if ($this->isAdmin()) {
            $rules += [
                'name'       => ['required', 'string', 'max:255'],
                'teacher_id' => ['required', 'exists:users,id'],
                'location'   => ['nullable', 'string', 'max:255'],
                'day'        => ['required', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
                'has_exam'   => ['nullable', 'boolean'],
            ];
        }

        return $rules;
    }

    /** Hanya admin yang boleh ganti pengajar. Selain itu tetap guru yang sekarang. */
    public function targetTeacherId(): ?int
    {
        $subject = $this->route('subject');

        return ($this->isAdmin() && $this->filled('teacher_id'))
            ? (int) $this->input('teacher_id')
            : $subject->teacher_id;
    }

    public function withValidator(Validator $validator): void
    {
        // Guru tidak bisa mengubah hari, jam, atau pengajar, jadi tidak ada yang perlu dicek bentrok.
        if (! $this->isAdmin()) {
            return;
        }

        $subject = $this->route('subject');

        $this->checkScheduleConflicts(
            $validator,
            $subject->academic_year_id,    // tahun ajaran milik jadwal itu sendiri
            $this->targetTeacherId(),
            $subject->id,                  // jangan bentrok dengan dirinya sendiri
        );
    }
}