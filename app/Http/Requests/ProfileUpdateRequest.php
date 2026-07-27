<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'digits_between:10,15',
                Rule::when($user->phone, ['in:' . $user->phone]),
            ],

            'school_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::when($user->school_name, ['in:' . $user->school_name]),
            ],

            'student_id' => [
                'nullable',
                'digits_between:5,20',
                Rule::when($user->student_id, ['in:' . $user->student_id]),
            ],

            'class_id' => [
                'nullable',
                'exists:school_classes,id',
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.digits_between' => 'Nomor telepon harus terdiri dari 10-15 digit.',
            'student_id.digits_between' => 'NIS harus berupa angka.',
            'phone.in' => 'Nomor telepon tidak dapat diubah.',
            'school_name.in' => 'Sekolah tidak dapat diubah.',
            'student_id.in' => 'NIS tidak dapat diubah.',
        ];
    }
}