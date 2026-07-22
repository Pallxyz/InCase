<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'teacher';
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            'class_id' => [
                'required',
                'exists:school_classes,id',
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
                'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
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

            'items' => [
                'nullable',
                'array',
            ],

            'items.*' => [
                'exists:items,id',
            ],

        ];
    }
}
