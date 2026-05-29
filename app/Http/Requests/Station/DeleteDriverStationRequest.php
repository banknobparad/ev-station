<?php

namespace App\Http\Requests\Station;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDriverStationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'กรุณากรอกเหตุผลที่ต้องการลบ',
        ];
    }
}
