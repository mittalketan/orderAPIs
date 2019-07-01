<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('orders', 'OrderController@store');

Route::patch('orders/{id}', 'OrderController@takeOrder');

Route::get('orders', 'OrderController@listOrders');

Route::fallback(function () {
    return response()->json(['error' => 'Not_Found'], 404);
});