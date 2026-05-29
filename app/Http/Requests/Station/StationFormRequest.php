<?php

namespace App\Http\Requests\Station;

use App\Services\StationService;
use Illuminate\Foundation\Http\FormRequest;

abstract class StationFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('connectors')) {
            $this->merge([
                'connectors' => StationService::normalizeConnectors($this->input('connectors', [])),
            ]);
        }
    }

    protected function baseRules(bool $withConnectors = true): array
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'address'          => 'required|string',
            'lat'              => 'required|numeric',
            'lng'              => 'required|numeric',
            'open_time'        => 'nullable',
            'close_time'       => 'nullable',
            'image'            => 'nullable|image|max:10240',
            'gallery_images'   => 'nullable|array',
            'gallery_images.*' => 'nullable|image|max:10240',
            'facilities'       => 'nullable|array',
            'facilities.*'     => 'integer|exists:facilities,id',
        ];

        if ($withConnectors) {
            $rules['connectors'] = 'nullable|array';
            $rules['connectors.*.type'] = 'required_with:connectors.*.total|in:CCS2,CHAdeMO,Type2,GB/T';
            $rules['connectors.*.total'] = 'required_with:connectors.*.type|integer|min:1';
        }

        return $rules;
    }
}
