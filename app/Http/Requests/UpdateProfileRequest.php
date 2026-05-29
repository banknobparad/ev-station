<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullable = [];
        foreach (['email', 'citizen_id', 'birth_date'] as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $nullable[$field] = null;
            }
        }
        $this->merge($nullable);
    }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $isProvider = $this->user()->role === 'provider';

        return [
            'name'       => 'required|string|max:255',
            'email'      => [
                $isProvider ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'      => [
                'required',
                'digits:10',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'citizen_id' => [
                'nullable',
                'digits:13',
                Rule::unique('users', 'citizen_id')->ignore($userId),
            ],
            'birth_date' => 'nullable|date|before:today',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'กรุณากรอกชื่อ',
            'email.required'       => 'กรุณากรอกอีเมล',
            'email.unique'         => 'อีเมลนี้ถูกใช้งานแล้ว',
            'phone.required'       => 'กรุณากรอกเบอร์โทร',
            'phone.digits'         => 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก',
            'phone.unique'         => 'เบอร์โทรนี้ถูกใช้งานแล้ว',
            'citizen_id.digits'    => 'เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'citizen_id.unique'    => 'เลขบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'birth_date.date'      => 'รูปแบบวันเกิดไม่ถูกต้อง',
            'birth_date.before'    => 'วันเกิดต้องเป็นวันในอดีต',
        ];
    }
}
