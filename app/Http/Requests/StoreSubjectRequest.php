<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksScheduleConflicts;
use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSubjectRequest extends FormRequest
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
            // Admin WAJIB memilih guru pengajar. Guru tidak mengisi ini sendiri
            // (controller yang mengunci teacher_id = dirinya sendiri).
            'teacher_id' => [
                Rule::requiredIf(fn () => $this->user()?->role === 'admin'),
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

    /** Guru: selalu dirinya sendiri. Admin: guru yang dipilih di form. */
    public function targetTeacherId(): ?int
    {
        return $this->user()->role === 'admin'
            ? (int) $this->input('teacher_id')
            : $this->user()->id;
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkScheduleConflicts(
            $validator,
            AcademicYear::active()?->id,   // jadwal baru selalu masuk tahun ajaran aktif
            $this->targetTeacherId(),
        );
    }
}