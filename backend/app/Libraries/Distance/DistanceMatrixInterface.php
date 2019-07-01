<?php

namespace App\Libraries\Distance;

interface DistanceMatrixInterface
{
    /**
     * Returns distance between Origin and Destination using Google Map Api else return Error
     *
     * @param array $origin
     * @param array $destination
     *
     * @return mixed
     */
    public function getDistance($origin, $destination);
}
