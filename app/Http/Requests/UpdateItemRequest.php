<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => 'required|max:255',

            'category' => 'required|in:paket,tulis,lks',

            'rfid_uid' => [
            'nullable',
            'string',
            'max:255',
            Rule::unique('items', 'rfid_uid')->ignore($this->route('item')),
        ],

            'quantity' => 'required|integer|min:0',

            'description' => 'nullable|max:500',

            'status' => 'required|in:active,archived',
        ];
    }
}