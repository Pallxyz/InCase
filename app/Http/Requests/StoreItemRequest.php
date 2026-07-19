<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',

            'category' => 'required|in:Book,Stationery,Electronics,Sports,Personal,Others',

            'rfid_uid' => 'required|unique:items,rfid_uid',

            'description' => 'nullable|max:500',

            'status' => 'required|in:active,archived',
        ];
    }
}