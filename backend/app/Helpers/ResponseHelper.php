<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Lang;

/**
 * Class to format response as per need
 */
class ResponseHelper
{

    /**
     * errorResponse generate api response as error with given messages and code
     * @param  string $messageCode     
     * @param  int $responseCode 
     * @return json 
     */
    public function errorResponse($messageCode, $responseCode = JsonResponse::HTTP_BAD_REQUEST)
    {

        //get error message from localize file if key exist else show given error
        $message = $this->getLocaleMessage($messageCode);

        $response = ['error' => $message];
        return response()->json($response, $responseCode);
    }

    /**
     * successResponse generate api success response with given data, messages and code
     * @param  string $messageCode    
     * @param  int $responseCode   
     * @param  array|null $responseData
     * @return json 
     */
    public function successResponse($messageCode, $responseCode = JsonResponse::HTTP_OK, $responseData = null)
    {
        //if have some response then send it directly
        if ($responseData !== null) {
            return response()->json($responseData, $responseCode);
        } else {
            //get message from localize file if key exist else show given error
            $successMessage = $this->getLocaleMessage($messageCode);
            $response       = ['status' => $successMessage];
            return response()->json($response, $responseCode);
        }
    }

    public function getLocaleMessage($key)
    {
        return Lang::has('message.' . $key) ? __('message.' . $key) : $key;
    }
}
