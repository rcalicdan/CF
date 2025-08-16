<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientFormRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255', 'regex:/^[0-9]+$/'],
            'postal_code' => ['nullable', 'string', 'max:255', 'regex:/^[0-9]+$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255', 'regex:/^[0-9]+$/'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
