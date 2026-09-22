<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Sesuaikan rule::in dengan value yang ada di option select form (huruf kecil)
            'category'    => ['required', 'string', Rule::in(['laptop', 'casan', 'tulis', 'paket'])],
            'rfid_uid'    => 'nullable|string|max:255|unique:items,rfid_uid',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => ['required', Rule::in(['active', 'archived'])],
        ];
    }
}