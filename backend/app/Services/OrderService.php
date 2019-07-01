<?php
namespace App\Services;

use App\Models\Order as OrderModel;
use App\Repositories\Order\OrderRepository;
use App\Services\DistanceService;
use Illuminate\Http\JsonResponse;

class OrderService
{

    /**
     *
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     *
     * @var DistanceService
     */
    private $distanceService;

    /**
     * OrderService Constructor
     *
     * @param OrderRepository $orderRepository
     * @param DistanceService $distanceService
     */
    public function __construct(OrderRepository $orderRepository, DistanceService $distanceService)
    {
        $this->orderRepository = $orderRepository;
        $this->distanceService = $distanceService;
    }

    /**
     * create an order
     *
     * @param  array $origin
     * @param  array $destination
     * @return mixed
     */
    public function createNewOrder(array $origin, array $destination)
    {
        // Get Distance Model
        $distance = $this->distanceService->getDistance($origin, $destination);

        // check for error
        if (!is_int($distance)) {
            $this->error     = $distance;
            $this->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
            return false;
        }

        $attribute = ['status' => OrderModel::UNASSIGNED_STATUS, 'distance' => $distance, 'start_latitude' => $origin[0], 'start_longtitude' => $origin[1], 'end_latitude' => $destination[0], 'end_longtitude' => $destination[1]];

        return $this->orderRepository->create($attribute);
    }

    /**
     * get the list of orders
     * @param  int $page
     * @param  int $limit
     * @return array
     */
    public function getList($page, $limit)
    {
        $page   = (int) $page;
        $limit  = (int) $limit;
        $orders = [];
        if ($page > 0 && $limit > 0) {
            $skip = ($page - 1) * $limit;
            return $this->orderRepository->list($skip, $limit);
        }
        return $orders;
    }

    /**
     * Assign Order
     * @param  int $id
     * @return bool
     */
    public function assignOrder(int $id)
    {
        $order = $this->orderRepository->findOrderByID($id);

        if (is_null($order)) {
            return null;
        }

        if ($order->status == OrderModel::ASSIGNED_STATUS) {
            return false;
        } else {
            return $this->orderRepository->assign($id);
        }
    }
}
