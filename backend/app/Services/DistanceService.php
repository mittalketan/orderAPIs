<?php
namespace App\Services;

use App\Libraries\Distance\DistanceMatrixInterface;

class DistanceService
{
    /**
     *
     * @var DistanceMatrix
     */
    private $distanceMatrix;

    /**
     * DistanceService Constructor
     *
     * @param DistanceMatrixInterface $DistanceMatrixInterface
     */
    public function __construct(DistanceMatrixInterface $DistanceMatrixInterface)
    {
        $this->distanceMatrix = $DistanceMatrixInterface;
    }

    /**
     * Returns distance between Origin and Destination
     *
     * @param array $origin
     * @param array $destination
     *
     * @return mixed
     */
    public function getDistance($origin, $destination)
    {
        $distance = $this->distanceMatrix->getDistance($origin, $destination);

        return $distance;
    }
}
