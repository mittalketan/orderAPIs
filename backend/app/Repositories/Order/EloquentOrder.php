<?php
namespace App\Repositories\Order;

use App\Models\Order as OrderModel;

/**
 * EloquentOrder Class to implement OrderRepository interface
 */
class EloquentOrder implements OrderRepository
{

    /**
     *
     * @var OrderModel
     */
    private $orderModel;

    /**
     * EloquentOrder Constructor
     *
     * @param OrderModel $order
     */
    public function __construct(OrderModel $order)
    {
        $this->orderModel = $order;
    }

    /**
     * Create a order
     *
     * @param array $attribute
     * @return OrderModel
     */
    public function create(array $attribute)
    {
        return $this->orderModel->create($attribute);
    }

    /**
     * Assign a order
     *
     * @param int $id
     * @return bool
     */
    public function assign(int $id)
    {
        $rows = $this->orderModel->where([
            ['id', '=', $id],
            ['status', '=', OrderModel::UNASSIGNED_STATUS],
        ])
            ->update(['orders.status' => OrderModel::ASSIGNED_STATUS]);

        return $rows > 0 ? true : false;
    }

    /**
     * find order by ID
     * @param  int $id
     * @return OrderModel
     */
    public function findOrderByID(int $id)
    {
        return $this->orderModel->find($id);
    }

    /**
     * Return list of Orders
     *
     * @param int $skip
     * @param int $limit
     * @return array
     */
    function list(int $skip, int $limit) {
        return $this->orderModel->skip($skip)->take($limit)->orderBy('id', 'ASC')->get()->toArray();
    }
}
