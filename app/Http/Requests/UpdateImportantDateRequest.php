<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImportantDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:anniversary,birthday,first_meet,first_date,custom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'recurring' => ['sometimes', 'boolean'],
        ];
    }
}
