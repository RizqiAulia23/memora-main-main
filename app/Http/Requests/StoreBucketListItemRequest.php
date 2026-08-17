<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBucketListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
