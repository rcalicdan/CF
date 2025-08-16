<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverFormRequest extends FormRequest
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
            'license_number' => [
                'required',
                'string',
                "regex:/^[0-9\/-]+$/",
                Rule::unique('drivers', 'license_number')->ignore($this->route('driver')),
            ],
            'vehicle_details' => ['sometimes', 'required', 'string', 'max:500'],
            'phone_number' => ['sometimes', 'required', 'string', 'regex:/^[0-9+-]+$/', 'max:30'],
        ];
    }
}
