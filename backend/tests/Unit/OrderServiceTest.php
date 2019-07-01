<?php

namespace Tests\Unit;

use App\Models\Order;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use WithoutMiddleware;

    protected static $orderStatus = [
        Order::UNASSIGNED_STATUS,
        Order::ASSIGNED_STATUS,
    ];

    protected $distanceService;
    protected $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker           = Faker::create();
        $this->distanceService = $this->createMock(\App\Services\DistanceService::class);
        $this->orderRepository = $this->createMock(\App\Repositories\Order\OrderRepository::class);
    }

    /**
     * Service::OrderService - Method:createOrder - PositiveTestCase
     *
     * @return void
     */
    public function testCreateOrder_PositiveTestCase()
    {
        echo "\n *** Unit Test Cases -- Order Service  *** \n";

        echo "\n *** Unit Test - Service::OrderService - Method:createOrder - PositiveTestCase (Valid Coordinates)*** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();
        $order               = $this->generateDummyOrder();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceService->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn($distanceCoordinates['distance']);

        $attribute = ['status' => self::$orderStatus[0], 'distance' => $distanceCoordinates['distance'], 'start_latitude' => $distanceCoordinates['origin'][0], 'start_longtitude' => $distanceCoordinates['origin'][1], 'end_latitude' => $distanceCoordinates['destination'][0], 'end_longtitude' => $distanceCoordinates['destination'][1]];

        $this->orderRepository->method('create')->with($attribute)->willReturn($order);

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $this->assertInstanceOf('\App\Models\Order', $orderService->createNewOrder($origin, $destination));
    }

    /**
     * Service::OrderService - Method:createOrder - NegativeTestCase
     *
     * @return void
     */
    public function testCreateOrder_NegativeCase_InvalidCoordinates()
    {
        echo "\n *** Unit Test - Service::OrderService - Method:createOrder - NegativeTestCase (Invalid coordinates) *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = implode(',', $distanceCoordinates['origin']);
        $destination = implode(',', $distanceCoordinates['destination']);

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $this->assertEquals(false, $orderService->createNewOrder($distanceCoordinates['origin'], $distanceCoordinates['destination']));
    }

    /**
     * Service::OrderService - Method:createOrder - NegativeTestCase
     *
     * @return void
     */
    public function testCreateOrder_Negative_InValidDistanceCal()
    {
        echo "\n *** Unit Test - Service::OrderService - Method:createOrder - NegativeTestCase (No Response from Google API) *** \n";

        $distanceCoordinates = $this->generateRamdomGeoCordinates();

        $origin      = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceService->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn('GOOGLE_API_NULL_RESPONSE');

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $this->assertEquals(false, $orderService->createNewOrder(
            $origin,
            $destination
        ));
    }

    /**
     * Service::OrderService - Method:getList - NegativeTestCase
     *
     * @return void
     */
    public function testGetList_NegativeCase()
    {
        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        echo "\n *** Unit Test - Service::OrderService - Method:getList - NegativeTestCase - With Invalid page variables *** \n";
        $this->assertEquals([], $orderService->getList('XAS', 2));

        echo "\n *** Unit Test - Service::OrderService - Method:getList -NegativeTestCase- With Invalid limit variables *** \n";
        $this->assertEquals([], $orderService->getList(2, 'DBNJJ'));
    }

    /**
     * Service::OrderService - Method:getList - PositiveCase
     *
     * @return void
     */
    public function testGetList_PositiveTestCase()
    {
        $page  = 1;
        $limit = 5;

        $orders = $this->arrayOfOrders($limit);
        echo "\n *** Unit Test - Service::OrderService - Method:getList - PositiveCase - With Valid page=1 and limit=5 variables *** \n";

        $this->orderRepository
            ->method('list')
            ->with($page - 1, $limit)
            ->willReturn($orders);

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $response = $orderService->getList($page, $limit);

        echo "\n \t Response Type should be an array\n";
        $this->assertInternalType('array', $response);

        echo "\n \t Response should count less than or equal to 5\n";
        $this->assertLessThanOrEqual(5, count($response));
    }

    /**
     * Service::OrderService - Method:assignOrder with NegativeTestCase (Invalid id)
     *
     * @return void
     */
    public function testassignOrder_NegativeTestCase_invalidID()
    {
        echo "\n *** Unit Test - Service::OrderService - Method:assignOrder with NegativeTestCase (Invalid id) *** \n";

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $id = $this->faker->numberBetween(499999, 999999);

        $this->orderRepository
            ->method('findOrderByID')
            ->with($id)
            ->willReturn(null);

        $this->assertEquals(null, $orderService->assignOrder($id));
    }

    /**
     * Service::OrderService - Method:assignOrder with NegativeTestCase (Already Taken)
     *
     * @return void
     */
    public function testassignOrder_NegativeTestCase_AlreadyTaken()
    {
        echo "\n *** Unit Test - Service::OrderService - Method:assignOrder with NegativeTestCase (Already Taken) *** \n";

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $id = $this->faker->numberBetween(1, 999);

        $order = $this->generateDummyOrder();

        //status should already taken
        $order->status = Order::ASSIGNED_STATUS;

        $this->orderRepository
            ->method('findOrderByID')
            ->with($id)
            ->willReturn($order);

        $this->assertEquals(false, $orderService->assignOrder($id));
    }

    /**
     * Service::OrderService - Method:assignOrder with PositiveCase
     *
     * @return void
     */
    public function testassignOrder_PositiveTestCase()
    {
        echo "\n *** Unit Test - Service::OrderService - Method:assignOrder with PositiveCase *** \n";

        $orderService = new \App\Services\OrderService($this->orderRepository, $this->distanceService);

        $id = $this->faker->numberBetween(1, 999);

        $order = $this->generateDummyOrder();

        //status should already taken
        $order->status = Order::UNASSIGNED_STATUS;

        $this->orderRepository
            ->method('findOrderByID')
            ->with($id)
            ->willReturn($order);

        $this->orderRepository
            ->method('assign')
            ->with($id)
            ->willReturn(true);

        $this->assertEquals(true, $orderService->assignOrder($id));
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

    /**
     * @param int|null $id
     *
     * @return Order
     */
    private function generateDummyOrder($id = null)
    {
        $id = $id ?: $this->faker->randomDigit();

        $order             = new Order();
        $order->id         = $id;
        $order->status     = $this->faker->randomElement(self::$orderStatus);
        $order->distance   = $this->faker->randomDigit();
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    private function arrayOfOrders($limit)
    {
        $orders = [];
        for ($i = 0; $i < $limit; $i++) {
            $orders[] = $this->generateDummyOrder();
        }
        return $orders;
    }
}
