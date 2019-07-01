<?php

namespace Tests\Unit;

use App\Http\Controllers\OrderController;
use App\Models\Order;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use WithoutMiddleware;

    protected static $allowedOrderStatus = [
        Order::UNASSIGNED_STATUS,
        Order::ASSIGNED_STATUS,
    ];

    public $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker          = Faker::create();
        $this->orderService   = \Mockery::mock(\App\Services\OrderService::class);
        $this->responseHelper = \App::make(\App\Helpers\ResponseHelper::class);

        $this->app->instance(
            OrderController::class,
            new OrderController(
                $this->orderService,
                $this->responseHelper
            )
        );
    }

    /**
     * Controller::OrderController - Method::store Postive Test Case
     *
     * @return void
     */
    public function testStore_PositiveTestCase()
    {
        echo "\n *** Unit Test Cases -- Order Controller  *** \n";

        echo "\n *** Unit Test - Controller::OrderController - Method::store -  Postive Test Case - *** \n";

        $order = $this->generateDummyOrder();

        $params = [
            'origin'      => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return success
        $this->orderService
            ->shouldReceive('createNewOrder')
            ->with($params['origin'], $params['destination'])
            ->once()
            ->andReturn($order);

        $response = $this->call('POST', '/orders', $params);
        $data     = $response->decodeResponseJson();

        $response->assertStatus(200);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    /**
     * Controller::OrderController - Method::store Negative Test Case
     *
     * @return void
     */
    public function testStore_NegativeTestCase_NoDestination()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method::store -  Negative Test Case - *** \n";

        $order = $this->generateDummyOrder();

        $params = [
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderService
            ->shouldReceive('createNewOrder')
            ->andReturn(false);

        $this->orderService->error     = 'INVALID_PARAMETERS';
        $this->orderService->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Controller::OrderController - Method::store Negative Test Case Exception Handling
     *
     * @return void
     */
    public function testStore_NegativeTestCase_Exception()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method::store -  Negative Test Case- Exception Handling - *** \n";

        $order = $this->generateDummyOrder();

        $params = [
            'origin'      => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderService
            ->shouldReceive('createNewOrder')
            ->andThrow(
                new \InvalidArgumentException()
            );

        $this->orderService->error     = 'Invalid_Argument_Exception';
        $this->orderService->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Controller::OrderController - Method::store Negative Test Case
     *
     * @return void
     */
    public function testStore_NegativeTestCase_InvalidCoodinates()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method::store -  Negative Test Case - *** \n";

        $order = $this->generateDummyOrder();

        $params = [
            'origin'      => [strval($this->faker->latitude(100)), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderService
            ->shouldReceive('createNewOrder')
            ->andReturn(false);

        $this->orderService->error     = 'INVALID_PARAMETERS';
        $this->orderService->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Controller::OrderController - Method::store Negative Test Case
     *
     * @return void
     */
    public function testStore_NegativeTestCase_validCoodinates_NoResponse()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method::store -  Negative Test Case - *** \n";

        $order = $this->generateDummyOrder();

        $params = [
            'origin'      => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderService
            ->shouldReceive('createNewOrder')
            ->andReturn(false);

        $this->orderService->error     = 'GOOGLE_MAP_API_NO_RESPONSE';
        $this->orderService->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Controller::OrderController - Method:takeOrder Positive Test Case
     *
     * @return void
     */
    public function testAssignOrder_PositiveCase()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with - PositiveTestCase - *** \n";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateDummyOrder($id);

        $order->status = Order::UNASSIGNED_STATUS;

        $this->orderService
            ->shouldReceive('assignOrder')
            ->once()
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('SUCCESS', $data['status']);
    }

    /**
     * Controller::OrderController - Method::takeOrder Negative Test Case Exception Handling
     *
     * @return void
     */
    public function testAssignOrder_NegativeTestCase_Exception()
    {

        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with - NegativeCase Exception Handling - *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateDummyOrder($id);

        //Order Service will return failure
        $this->orderService
            ->shouldReceive('assignOrder')
            ->with($id)
            ->andThrow(
                new \InvalidArgumentException()
            );

        $this->orderService->error     = 'Invalid_Argument_Exception';
        $this->orderService->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);

    }

    /**
     * Controller::OrderController - Method:takeOrder Negative Test Case
     *
     * @return void
     */
    public function testAssignOrder_NegativeCase_invalidParamater()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with - NegativeCase (Invalid Input Parameter) - *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateDummyOrder($id);

        $this->orderService
            ->shouldReceive('assignOrder')
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'ASSIGNED'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_STATUS', $data['error']);
    }

    /**
     * Controller::OrderController - Method:takeOrder Negative Test Case
     *
     * @return void
     */
    public function testAssignOrder_NegativeCase_StringAsID()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with NegativeTestCase (id As string) *** \n";

        $id = 'A';

        $order = $this->generateDummyOrder($id);

        $this->orderService
            ->shouldReceive('assignOrder')
            ->with($id)
            ->andReturn(null);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_ORDER_ID_TYPE', $data['error']);
    }

    /**
     * Controller::OrderController - Method:takeOrder Negative Test Case
     *
     * @return void
     */
    public function testAssignOrder_NegativeCase_invalidID()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with NegativeTestCase (Invalid id) *** \n";

        $id = $this->faker->numberBetween(499999, 999999);

        $order = $this->generateDummyOrder($id);

        $this->orderService
            ->shouldReceive('assignOrder')
            ->with($id)
            ->andReturn(null);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_ORDER_ID', $data['error']);
    }

    /**
     * Controller::OrderController - Method:takeOrder Negative Test Case
     *
     * @return void
     */
    public function testAssignOrder_NegativeTestCase_AlreadyTaken()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:takeOrder with NegativeTestCase (Already Taken) *** \n";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateDummyOrder($id);

        //status should already taken
        $order->status = Order::ASSIGNED_STATUS;

        //In Valid order id provided
        $this->orderService
            ->shouldReceive('assignOrder')
            ->once()
            ->with($id)
            ->andReturn(false);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data     = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('ALREADY_TAKEN', $data['error']);
    }

    /**
     * Controller::OrderController - Method:listOrders Positive Test Case
     *
     * @return void
     */
    public function testListOrders_PositiveTestCase()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - with PositiveTestCase *** \n";

        $page  = 1;
        $limit = 5;

        $orderList = [];

        for ($i = 0; $i < 5; $i++) {
            $orderList[] = $this->generateDummyOrder();
        }

        //In Valid order id provided
        $this->orderService
            ->shouldReceive('getList')
            ->once()
            ->with($page, $limit)
            ->andReturn($orderList);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertInternalType('array', $data);

        $this->assertArrayHasKey('id', (array) $data[0]);
        $this->assertArrayHasKey('distance', (array) $data[0]);
        $this->assertArrayHasKey('status', (array) $data[0]);
    }

    /**
     * Controller::OrderController - Method::listOrders Negative Test Case Exception Handling
     *
     * @return void
     */
    public function testlistOrders_NegativeTestCase_Exception()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - Exception Handling*** \n";

        $page  = 34;
        $limit = 5;

        //In Valid order id provided
        $this->orderService
            ->shouldReceive('getList')
            ->once()
            ->with($page, $limit)
            ->andThrow(
                new \InvalidArgumentException()
            );

        $this->orderService->error     = 'Invalid_Argument_Exception';
        $this->orderService->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);

    }

    /**
     * Controller::OrderController - Method:listOrders Positive Test Case
     *
     * @return void
     */
    public function testListOrders_PositiveTest_Nodata()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - PositiveTest (No Param) *** \n";

        $page  = 599999;
        $limit = 5;

        $orderList = [];

        //In Valid order id provided
        $this->orderService
            ->shouldReceive('getList')
            ->once()
            ->with($page, $limit)
            ->andReturn($orderList);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_OK);
    }

    /**
     * Controller::OrderController - Method:listOrders Negative Test Case
     *
     * @return void
     */
    public function testListOrders_NegativeTest_InvalidPageParam()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - with NegativeTest (Invalid Page Param) *** \n";

        $page  = 'X';
        $limit = 5;

        $orderList = [];

        //In Valid order id provided
        $this->orderService
            ->shouldReceive('getList')
            ->andReturn($orderList);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
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
        $order->status     = $this->faker->randomElement(self::$allowedOrderStatus);
        $order->distance   = $this->faker->randomDigit();
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }
}
