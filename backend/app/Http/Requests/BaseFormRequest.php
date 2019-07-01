<?php

namespace App\Http\Requests;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BaseFormRequest extends FormRequest
{

    /** @var Response */
    protected $responseHelper;

    /**
     * @param Response $responseHelper
     */
    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Overriding response of validation
     * @param  Validator
     * @return  Json
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        //Currectly considering only first error
        $firstError = array_values($errors)[0][0];

        throw new HttpResponseException(
            $this->responseHelper->errorResponse($firstError, JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
