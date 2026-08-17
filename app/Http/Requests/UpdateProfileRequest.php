<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required_without:avatar', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'relationship_date' => ['nullable', 'date', 'before_or_equal:today'],
            'location' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
