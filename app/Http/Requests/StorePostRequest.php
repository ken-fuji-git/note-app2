<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'category' => 'required|in:tech,life,idea',
            'body' => [
                'required',
                function ($attribute, $value, $fail) {
                    $stripped = strip_tags($value);
                    $converted = mb_convert_kana($stripped, 's');
                    if (empty(trim($converted))) {
                        $fail('本文は必須入力です。');
                    }
                },
            ],
            'is_published' => 'nullable|boolean',

        ];
    }
}
