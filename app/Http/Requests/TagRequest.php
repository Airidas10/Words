<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
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

        if (!in_array($this->method(), ['DELETE'])){
            $rules = [
                'tag' => [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('tags', 'tag')->ignore($this->route('id')),
                ],
            ];
        }

        return $rules;
    }
}
