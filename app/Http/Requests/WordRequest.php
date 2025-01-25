<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WordRequest extends FormRequest
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
        $rules = [];

        if(!in_array($this->method(), ['DELETE'])){
            $rules = [
                'word' => 'required|string|max:191',
                'translation' => 'required|string|max:191',
                'description' => 'nullable|string|max:65535',
                'tags' => 'present|array',
                'tags.*.id' => 'required|exists:tags,id',
            ];
        }

        return $rules;
    }
}
