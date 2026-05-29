<?php

namespace App\Http\Requests\Station;

use Illuminate\Foundation\Http\FormRequest;

class ConnectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'  => 'required|in:CCS2,CHAdeMO,Type2,GB/T',
            'total' => 'required|integer|min:1',
        ];
    }
}
