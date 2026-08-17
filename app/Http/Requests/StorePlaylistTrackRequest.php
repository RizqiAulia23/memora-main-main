<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaylistTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'artist' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:500'],
            'thumbnail' => ['nullable', 'url', 'max:500'],
        ];
    }
}
