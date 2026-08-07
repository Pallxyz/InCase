<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- Tambahkan baris ini

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|max:255',
            'category'    => ['required', 'string', Rule::in(['paket', 'tulis', 'lks'])],
            'rfid_uid' => 'nullable|string|max:255|unique:items,rfid_uid',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => ['required', Rule::in(['active', 'archived'])],
        ];
    }
}