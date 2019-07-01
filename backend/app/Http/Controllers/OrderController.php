<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AssignOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\ListOrdersRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    /**
     *
     * @var OrderService
     */
    private $orderService;

    /**
     *
     * @var ResponseHelper
     */
    private $responseHelper;

    public function __construct(OrderService $orderService, ResponseHelper $responseHelper)
    {
        $this->orderService   = $orderService;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Store a newly created Order in storage.
     *
     * @param  App\Http\Requests\CreateOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $origin      = $request->input('origin');
            $destination = $request->input('destination');

            $order = $this->orderService->createNewOrder($origin, $destination);
            if ($order) {
                return $this->responseHelper->successResponse('Success', JsonResponse::HTTP_OK, $order);
            } else {
                return $this->responseHelper->errorResponse($this->orderService->error, $this->orderService->errorCode);
            }
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return $this->responseHelper->errorResponse($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified Order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function takeOrder(AssignOrderRequest $request, $id)
    {
        try {
            $validator = \Validator::make(
                [
                    'id' => $id,
                ],
                [
                    'id' => 'required|int',
                ],
                [
                    'id.required' => 'REQ_ORDER_ID_PARAM',
                    'id.integer'  => 'INVALID_ORDER_ID_TYPE',
                ]
            );

            if ($validator->fails()) {
                $errors     = (new ValidationException($validator))->errors();
                $firstError = array_values($errors)[0][0];
                return $this->responseHelper->errorResponse($firstError, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $order = $this->orderService->assignOrder($id);

            if (is_null($order)) {
                return $this->responseHelper->errorResponse('INVALID_ORDER_ID', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($order) {
                return $this->responseHelper->successResponse('Success', JsonResponse::HTTP_OK, ["status" => "SUCCESS"]);
            } else {
                return $this->responseHelper->errorResponse('ALREADY_TAKEN', JsonResponse::HTTP_CONFLICT);
            }
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return $this->responseHelper->errorResponse($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the Orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function listOrders(ListOrdersRequest $request)
    {
        try {
            $page  = (int) $request->get('page', 1);
            $limit = (int) $request->get('limit', 1);

            $orders = $this->orderService->getList($page, $limit);

            return $this->responseHelper->successResponse('Success', JsonResponse::HTTP_OK, $orders);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return $this->responseHelper->errorResponse($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
