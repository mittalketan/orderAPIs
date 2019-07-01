<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderIntegrationTest extends TestCase
{
    public function testCreateOrderIncorrectParameters()
    {
        echo "\n *** Integration Test Cases *** \n";

        echo "\n \n \n*** Create Order (Positive and Negative) *** \n";

        echo "\n > Create Order - Negative Case - With Invalid Keys - Should get 422 Code";

        $Postdata = [
            'originn' => ['18.704060', '77.102493'],
            'destination' => ['21.555337','73.321029'],
        ];

        $response = $this->json('POST', '/orders', $Postdata);

        $response->assertStatus(422);
    }

    public function testCreateOrderEmptyParameters()
    {
        echo "\n\n > Create Order - Negative Case - Without Coordinates - Should get 422 Code";

        $Postdata = [
            'origin' => ['18.704060', ''],
            'destination' => ['18.555337','73.321029'],
        ];

        $response = $this->json('POST', '/orders', $Postdata);

        $response->assertStatus(422);
    }


    public function testCreateOrderMultipleParameters()
    {
        echo "\n\n > Create Order - Negative Case - With Multiple Parameter value - Should get 422 Code";

        $Postdata = [
            'origin' => ['18.704060', '77.102493','77.102493'],
            'destination' => ['21.555337','73.321029'],
        ];

        $response = $this->json('POST', '/orders', $Postdata);

        $response->assertStatus(422);
    }


    public function testCreateOrderInvalidData()
    {
        echo "\n\n > Create Order - Negative Case - With Invalid Coordinates - Should get 422 Code";
        $Postdata = [
            'origin' => ['318.704060', '77.102493'],
            'destination' => ['21.555337','73.321029'],
        ];

        $response = $this->json('POST', '/orders', $Postdata);

        $response->assertStatus(422);
    }

    public function testOrderCreationPositiveScenario()
    {
        echo "\n\n > Create Order - Positive Case - With valid Coordinates - Should get 200 Code";

        $validData = [
            'origin' => ['28.704061', '77.102493'],
            'destination' => ['21.555337','73.321029'],
        ];

        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();

        $response->assertStatus(200);

        echo "\n\t > Response should have order details(id, status and distance)";
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    public function testAssignOrder()
    {
        echo "\n \n \n*** Executing Assign Order Scenario (Positive and Negative) *** \n";

        echo "\n > Assign Order Positive Test - Valid Data \n";

        $validData = [
            'origin' => ['23.704060', '76.102493'],
            'destination' => ['21.555337','73.321029'],
        ];

        $updateData = ['status' => 'TAKEN'];

        echo "\n > Creating an new order";

        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();
        $id = $data['id'];

        echo "\n > Order has been created with id : ".$id;

        echo "\n\n > Assigning Order \n";
        $response = $this->json('PATCH', '/orders/'. $id, $updateData);
        $data = (array) $response->getData();

        echo "\n\t > Assign Order - should have status 200  Code";
        $response->assertStatus(200);

        echo "\n\t > Assign Order - response has key as `status`";
        $this->assertArrayHasKey('status', $data);

        echo "\n\n\n > Order Assign Negative Test\n";

        $updateData = ['status' => 'TAKEN'];

        echo "\n \t > Trying to assign same order - should has status 409 Code";

        $response = $this->json('PATCH', '/orders/'. $id, $updateData);
        $data = (array) $response->getData();
        $response->assertStatus(409);

        echo "\n \t > TryiAssign ng to same order - response should has key `error`";
        $this->assertArrayHasKey('error', $data);

        echo "\n\n > Assign Order Negative Test - Invalid Params key (status1)";
        $this->assignOrderNegativeCase($id, ['status1' => 'TAKEN'], $expectedCode = 422);

        echo "\n\n > Assign Order Negative Test - Invalid Param value (TAKEN1)";
        $this->assignOrderNegativeCase($id, ['status' => 'TAKEN1'], $expectedCode = 422);

        echo "\n\n > Assign Order Negative Test - Empty Param value \n";
        $this->assignOrderNegativeCase($id, ['status' => ''], $expectedCode = 422);

        echo "\n\n > Assign Order Negative Test - Invalid Order id \n";
        $this->assignOrderNegativeCase(343, ['status' => 'TAKEN'], $expectedCode = 422);
    }

    protected function assignOrderNegativeCase($id, $params, $expectedCode)
    {
        $response = $this->json('PATCH', '/orders/'. $id, $params);
        $data = (array) $response->getData();

        echo "\n\t > Trying to assign Invalid Order - response should has status $expectedCode";
        $response->assertStatus($expectedCode);

        echo "\n\t > Trying to assign Invalid Order - response should has key `error`";
        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderListSuccessCount()
    {
        echo "\n \n \n*** Order List (Positive and Negative) *** \n";

        echo "\n > Order Listing Positive Test - Valid Data Count(page=1&limit=4) \n";

        $query = 'page=1&limit=4';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        echo "\n\t > Order Listing Positive Test - Should get status as 200 code";
        $response->assertStatus(200);

        echo "\n\t > Order Listing Positive Test - count of data should less than or equal to 4 ";
        $this->assertLessThan(5, count($data));
    }

    public function testOrderListSuccessData()
    {
        echo "\n\n > Order Listing Positive Test - Valid Data Keys (page=1&limit=3)";

        $query = 'page=1&limit=3';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        echo "\t > Status should be 200 Code\n";
        $response->assertStatus(200);

        foreach ($data as $order) {
            $order = (array) $order;
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('distance', $order);
            $this->assertArrayHasKey('status', $order);
        }
    }


    public function testOrderListSuccessNoData()
    {
        echo "\n > Order Listing Positive Test - Valid Data Keys (page=34999&limit=10) -- return blank array ";

        $query = 'page=34999&limit=10';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        echo "\n\t > Status should be 200\n";
        $response->assertStatus(200);
    }

    public function testOrderListFailure()
    {
        echo "\n > Order Listing Negative Test - Invalid Params (pag) - Should get 422 Code\n";
        $query = 'pag=1&limit=6';
        $this->orderListFailure($query, 422);

        echo "\n > Order Listing Negative Test - Invalid Params (limitt) - Should get 422 Code\n";
        $query = 'page=1&limitt=6';
        $this->orderListFailure($query, 422);

        echo "\n > Order Listing Negative Test - Invalid Params (page = 0) - Should get 422 Code\n";
        $query = 'page=0&limit=6';
        $this->orderListFailure($query, 422);

        echo "\n > Order Listing Negative Test - Invalid Params  (limit = 0) - Should get 422 Code\n";
        $query = 'page=1&limit=0';
        $this->orderListFailure($query, 422);

        echo "\n > Order Listing Negative Test - Invalid Params  (limit = -1) - Should get 422 Code\n";
        $query = 'page=1&limit=-1';
        $this->orderListFailure($query, 422);

        echo "\n > Order Listing Negative Test - Invalid Params  (page = -3) - Should get 422 Code\n";
        $query = 'page=-3&limit=0';
        $this->orderListFailure($query, 422);
    }

    protected function orderListFailure($query, $expectedCode)
    {
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus($expectedCode);
    }
}
