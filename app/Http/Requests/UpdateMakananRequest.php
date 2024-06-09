<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMakananRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'image' => 'image|mimes:jpeg,jpg,png|max:2048',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
        ];
    }
}
