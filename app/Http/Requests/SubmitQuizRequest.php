<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'No answers were submitted.',
            'answers.array' => 'Invalid answer format.',
        ];
    }
}
