<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksScheduleConflicts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubjectRequest extends FormRequest
{
    use ChecksScheduleConflicts;

    public function authorize(): bool
    {
        return Auth::check()
            && in_array(Auth::user()->role, ['teacher', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'exists:school_classes,id',
            ],
            // Cuma admin yang boleh memindahkan jadwal ke guru lain.
            'teacher_id' => [
                'sometimes',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'teacher')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'homework' => [
                'nullable',
                'string',
            ],
            'has_exam' => [
                'nullable',
                'boolean',
            ],
            'day' => [
                'required',
                Rule::in($this->user()?->school()?->dayNames() ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'required_items' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /** Guru selain admin tidak mengirim teacher_id -> tetap milik guru itu sendiri. */
    public function targetTeacherId(): ?int
    {
        $subject = $this->route('subject');

        return $this->filled('teacher_id')
            ? (int) $this->input('teacher_id')
            : $subject->teacher_id;
    }

    public function withValidator(Validator $validator): void
    {
        $subject = $this->route('subject');

        $this->checkScheduleConflicts(
            $validator,
            $subject->academic_year_id,    // tahun ajaran milik jadwal itu sendiri
            $this->targetTeacherId(),
            $subject->id,                  // jangan bentrok dengan dirinya sendiri
        );
    }
}