<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidateCoordinateRule;

class CreateOrderRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "origin"        => ["required", "array", "min:2", new ValidateCoordinateRule],
            "origin.*"      => "required|string",
            "destination"   => ["required", "array", "min:2", new ValidateCoordinateRule],
            "destination.*" => "required|string",

        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'origin.required'        => 'REQ_ORIGIN',
            'origin.*.required'      => 'REQ_ORIGIN',
            'origin.*.string'        => 'ORIGIN_INVALID_PARAMETERS',
            'origin.min'             => 'ORIGIN_INVALID_PARAMETERS',

            'destination.required'   => 'REQ_DESTINATION',
            'destination.*.required' => 'REQ_DESTINATION',
            'destination.*.string'   => 'DESTINATION_INVALID_PARAMETERS',
            'destination.min'        => 'DESTINATION_INVALID_PARAMETERS',
        ];
    }
}
