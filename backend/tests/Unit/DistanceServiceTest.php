<?php

namespace Tests\Unit;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class DistanceServiceTest extends TestCase
{
    use WithoutMiddleware;

    protected $distanceMatrix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker          = Faker::create();
        $this->distanceMatrix = $this->createMock(\App\Libraries\Distance\DistanceMatrixInterface::class);
    }

    /**
     * Service::DistanceService - Method:getDistance with --- INVALID LAT LONG
     *
     * @return void
     */
    public function testGetDistanceWithInvalidData_OutOfRange()
    {
        echo "\n *** Unit Test Cases -- Distance Service  *** \n";
        echo "\n *** Unit Test - Service::DistanceService - Method:getDistance with --- INVALID LAT LONG (Out of range) --- *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceMatrix->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn('GOOGLE_MAP_API_NULL_RESPONSE');

        $distanceService = new \App\Services\DistanceService($this->distanceMatrix);
        $distance        = $distanceService->getDistance($origin, $destination);

        $this->assertRegExp("/^GOOGLE_MAP_API(.*)/", $distance);
    }

    /**
     * Service::DistanceService - Method:getDistance with --- No LAT LONG
     *
     * @return void
     */
    public function testGetDistanceWithValidData_NoAPIResponce()
    {

        echo "\n *** Unit Test - Service::DistanceService - Method:getDistance with --- Valid Data No API Responce --- *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceMatrix->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn('GOOGLE_MAP_API.NO_RESPONSE');

        $distanceService = new \App\Services\DistanceService($this->distanceMatrix);
        $distance        = $distanceService->getDistance($origin, $destination);

        $this->assertRegExp("/^GOOGLE_MAP_API(.*)/", $distance);
    }
    /**
     * Service::DistanceService - Method:getDistance with --- No LAT LONG
     *
     * @return void
     */
    public function testGetDistanceWithInvalidData_NoLatLong()
    {

        echo "\n *** Unit Test - Service::DistanceService - Method:getDistance with --- No LAT LONG --- *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceMatrix->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn('GOOGLE_MAP_API_NULL_RESPONSE');

        $distanceService = new \App\Services\DistanceService($this->distanceMatrix);
        $distance        = $distanceService->getDistance($origin, $destination);

        $this->assertRegExp("/^GOOGLE_MAP_API(.*)/", $distance);
    }

    /**
     * Service::DistanceService - Method:getDistance with --- VALID LAT LONG
     *
     * @return void
     */
    public function testGetDistance_validData()
    {
        echo "\n *** Unit Test - Service::DistanceService - Method:getDistance with --- VALID LAT LONG --- *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceMatrix->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn($distanceCoordinates['distance']);

        $distanceService = new \App\Services\DistanceService($this->distanceMatrix);
        $distance        = $distanceService->getDistance($origin, $destination);

        $this->assertIsInt($distance);
    }

    /**
     * @return array
     */
    protected function generateRamdomGeoCordinates()
    {
        $initialLatitude  = $this->faker->latitude();
        $initialLongitude = $this->faker->latitude();
        $finalLatitude    = $this->faker->longitude();
        $finalLongitude   = $this->faker->longitude();

        $distance = $this->distance($initialLatitude, $initialLongitude, $finalLatitude, $finalLongitude);

        return [
            'origin'      => [$initialLatitude, $initialLongitude],
            'destination' => [$finalLatitude, $finalLongitude],
            'distance'    => $distance,
        ];
    }

    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     *
     * @return int
     */
    public function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta           = $lon1 - $lon2;
        $dist            = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist            = acos($dist);
        $dist            = rad2deg($dist);
        $distanceInMetre = $dist * 60 * 1.1515 * 1.609344 * 1000;

        return (int) $distanceInMetre;
    }
}
