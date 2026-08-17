<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:80'],
        ];
    }
}
