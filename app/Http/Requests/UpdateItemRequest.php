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

            'category' => 'required|in:Book,Stationery,Electronics,Sports,Personal,Others',

            'rfid_uid' => [
                'required',
                Rule::unique('items')->ignore($this->item),
            ],

            'description' => 'nullable|max:500',

            'status' => 'required|in:active,archived',
        ];
    }
}