<?php

namespace App\Http\Requests;

use App\Enums\LoveLetterMood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoveLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:50000'],
            'mood' => ['required', 'string', Rule::in(LoveLetterMood::values())],
            'letter_date' => ['required', 'date'],
            'is_pinned' => ['sometimes', 'boolean'],
        ];
    }
}
