<?php

namespace App\Http\Requests\Station;

class UpdateDriverStationRequest extends StationFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->baseRules(true);
    }
}
