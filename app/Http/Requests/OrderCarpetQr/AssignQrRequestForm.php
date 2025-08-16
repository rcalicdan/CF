<?php

namespace App\Http\Requests\OrderCarpetQr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignQrRequestForm extends FormRequest
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
            'qr_code' => ['required', 'string', Rule::unique('order_carpets', 'qr_code')],
        ];
    }
}
