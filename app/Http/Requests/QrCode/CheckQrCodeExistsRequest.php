<?php

namespace App\Http\Requests\QrCode;

use Illuminate\Foundation\Http\FormRequest;

class CheckQrCodeExistsRequest extends FormRequest
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
            'qr_code' => 'required|string|min:3|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'qr_code.required' => 'Reference code is required.',
            'qr_code.string' => 'Reference code must be a string.',
            'qr_code.min' => 'Reference code must be at least 3 characters long.',
            'qr_code.max' => 'Reference code cannot exceed 50 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'qr_code' => 'reference code',
        ];
    }
}