<?php

namespace App\Repositories\Order;

interface OrderRepository
{

    /**
     * Create a order
     *
     * @param array $attribute
     * @return OrderModel
     */
    public function create(array $attribute);

    /**
     * Assign a order
     *
     * @param int $id
     * @return bool
     */
    public function assign(int $id);

    /**
     * find order by ID
     * @param  int $id
     * @return OrderModel
     */
    public function findOrderByID(int $id);

    /**
     * Return a list of orders
     *
     * @param int $skip
     * @param int $limit
     * @return array
     */
    public function list(int $skip, int $limit);
}
