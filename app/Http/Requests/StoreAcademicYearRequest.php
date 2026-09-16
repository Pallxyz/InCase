<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_start' => ['required', 'integer', 'digits:4', 'min:2000'],
            'year_end' => ['required', 'integer', 'digits:4', 'gt:year_start'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
        ];
    }

    public function messages(): array
    {
        return [
            'year_end.gt' => 'Tahun akhir harus lebih besar dari tahun mulai.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\AcademicYear::where('year_start', $this->year_start)
                ->where('year_end', $this->year_end)
                ->where('semester', $this->semester)
                ->exists();

            if ($exists) {
                $validator->errors()->add('semester', 'Tahun ajaran dan semester ini sudah ada.');
            }
        });
    }
}