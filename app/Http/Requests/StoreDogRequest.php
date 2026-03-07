<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'photo' => 'required|image|max:5120',
            'gender' => 'required|in:オス,メス',
            'age' => 'required|integer|min:0|max:30',
            'breed' => 'required|string|max:50',
            'height' => 'required|integer|min:5|max:120',
            'personality' => 'required|string|max:100',
        ];
    }
}
