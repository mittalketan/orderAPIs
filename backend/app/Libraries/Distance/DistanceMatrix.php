<?php

namespace App\Libraries\Distance;

class DistanceMatrix implements DistanceMatrixInterface
{
    /**
     * Returns distance between Origin and Destination using Google Map Api else return Error
     *
     * @param array $origin
     * @param array $destination
     *
     * @return mixed
     */
    public function getDistance($origin, $destination)
    {
        try {

            $apiKey = env('GOOGLE_MAP_KEY');
            $apiURL = env('GOOGLE_MAP_API_URL');

            if (isset($apiKey) && isset($apiURL)) {

                $url = $apiURL . "?units=imperial&origins=" . implode(",", $origin) . "&destinations=" . implode(",", $destination) . "&key=" . $apiKey;

                $data = file_get_contents($url);

                $data = json_decode($data);
                if (!empty($data) && isset($data->status)) {
                    if ('OK' == trim($data->status)) {
                        $dataElements = $data->rows[0]->elements[0];
                        if (isset($dataElements->distance->value)) {
                            return (int) $dataElements->distance->value;
                        } else {
                            return "GOOGLE_MAP_API_NO_RESPONSE";
                        }
                    } else {
                        return "GOOGLE_MAP_API_" . $data->status;
                    }
                } else {
                    return "GOOGLE_MAP_API_NO_RESPONSE";
                }
            } else {
                return "GOOGLE_MAP_API_KEY_MISSING";
            }
        } catch (\Exception $e) {
            return (isset($dataElements->status)) ? $dataElements->status : 'GOOGLE_MAP_API_NO_RESPONSE';
        }
    }
}
